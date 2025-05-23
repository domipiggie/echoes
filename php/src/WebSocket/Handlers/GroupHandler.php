<?php

namespace WebSocket\Handlers;

use Ratchet\ConnectionInterface;
use Utils\Logger;
use WebSocket\Services\ResponseHandlerService;
use WebSocket\Services\ErrorHandlerService;
use Utils\DatabaseOperations;
use WebSocket\Services\NotificationService;

class GroupHandler
{
    private $logger;
    private $notificationService;
    private $errorHandler;

    public function __construct($logger, $clients)
    {
        $this->logger = $logger;
        $this->notificationService = new NotificationService($clients, $logger);
        $this->errorHandler = new ErrorHandlerService($logger);
    }

    public function handleCreateGroupChannel(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['userIds', 'groupName'])) {
            return;
        }

        $sender = $from->userData;
        $userIds = $data['userIds'];
        $groupName = $data['groupName'];
        $groupPicture = isset($data['groupPicture']) ? $data['groupPicture'] : null;

        try {
            $validUserIds = $this->validateGroupMembers($sender, $userIds);
            
            $group = new \Group($this->logger);
            $channelId = $group->createGroupWithValidatedUsers($validUserIds, $groupName, $groupPicture, $sender->id);

            $this->sendGroupCreationResponse($from, $channelId, $groupName, $groupPicture, $validUserIds, $sender);
            
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'group_channel_create');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'GROUP_CREATION_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Group creation failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'group_channel_create');
        }
    }
    
    private function validateGroupMembers($sender, $userIds)
    {
        $validUserIds = [$sender->id];
        $group = new \Group($this->logger);

        foreach ($userIds as $userId) {
            if ($userId == $sender->id) {
                continue;
            }
            
            $group->validateFriendship($sender->id, $userId);
            $validUserIds[] = $userId;
        }
        
        return $validUserIds;
    }
    
    private function sendGroupCreationResponse($from, $channelId, $groupName, $groupPicture, $validUserIds, $sender)
    {
        ResponseHandlerService::sendSuccess($from, 'group_channel_created', [
            'channelId' => $channelId,
            'groupName' => $groupName,
            'groupPicture' => $groupPicture,
            'members' => $validUserIds
        ]);

        $this->logger->info("Group channel created by user {$sender->id} with " . count($validUserIds) . " members");

        $notifyData = [
            'channelId' => $channelId,
            'groupName' => $groupName,
            'groupPicture' => $groupPicture,
            'creator' => [
                'id' => $sender->id,
                'username' => $sender->username
            ],
            'members' => $validUserIds
        ];

        foreach ($validUserIds as $userId) {
            if ($userId != $sender->id) {
                $this->notificationService->notifyClient($userId, 'added_to_group', $notifyData);
            }
        }
    }
    
    public function handleAddUserToGroup(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['userIds', 'channelId'])) {
            return;
        }

        $sender = $from->userData;
        $userIds = $data['userIds'];
        $channelId = $data['channelId'];

        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        try {
            $group = new \Group($this->logger);
            $groupId = $group->getGroupIdFromChannel($channelId);
            
            if (!$group->isGroupOwner($sender->id, $groupId)) {
                throw new \WebSocketException(
                    "Only the group owner can add users to the group",
                    "NOT_GROUP_OWNER",
                    403
                );
            }
            
            $validUserIds = [];
            foreach ($userIds as $userId) {
                $group->validateFriendship($sender->id, $userId);
                $validUserIds[] = $userId;
            }
            
            $channel = new \Channel();
            $channel->addUsersToChannel($channelId, $validUserIds);
            
            $this->sendAddUsersResponse($from, $validUserIds, $groupId, $channelId, $sender);
            
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'add_user_to_group');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'ADD_USER_TO_GROUP_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Adding users to group failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'add_user_to_group');
        }
    }
    
    private function sendAddUsersResponse($from, $userIds, $groupId, $channelId, $sender)
    {
        ResponseHandlerService::sendSuccess($from, 'users_added_to_group', [
            'userIds' => $userIds,
            'groupId' => $groupId,
            'channelId' => $channelId
        ]);
        
        $this->logger->info("Users " . implode(', ', $userIds) . " added to group {$channelId} by user {$sender->id}");
        
        $group = new \Group($this->logger);
        
        foreach ($userIds as $userId) {
            $notifyData = [
                'channelId' => $channelId,
                'groupId' => $groupId,
                'addedBy' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ]
            ];
            
            $this->notificationService->notifyClient($userId, 'added_to_group', $notifyData);
        }
        
        $members = $group->getGroupMembers($channelId);
        $existingMembers = array_filter($members, function($member) use ($userIds, $sender) {
            return !in_array($member['userID'], $userIds) && $member['userID'] != $sender->id;
        });
        
        if (count($existingMembers) > 0) {
            $memberNotifyData = [
                'channelId' => $channelId,
                'groupId' => $groupId,
                'newMembers' => array_map(function($userId) {
                    return ['id' => $userId];
                }, $userIds),
                'addedBy' => [
                    'id' => $sender->id,
                    'username' => $sender->username
                ]
            ];
            
            foreach ($existingMembers as $member) {
                $this->notificationService->notifyClient($member['userID'], 'users_added_to_group', $memberNotifyData);
            }
        }
    }
    
    public function handleLeaveGroup(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['channelId'])) {
            return;
        }

        $sender = $from->userData;
        $channelId = $data['channelId'];

        try {
            $group = new \Group($this->logger);
            $groupId = $group->getGroupIdFromChannel($channelId);
            
            if ($group->isGroupOwner($sender->id, $groupId)) {
                throw new \WebSocketException(
                    "Group owner cannot leave the group. Transfer ownership first.",
                    "OWNER_CANNOT_LEAVE",
                    400
                );
            }
            
            $group->checkUserInGroup($sender->id, $channelId);
            
            $this->processUserRemoval($from, $sender->id, $groupId, $channelId, $group, true);
            
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'leave_group');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'LEAVE_GROUP_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Leaving group failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'leave_group');
        }
    }
    
    public function handleRemoveUserFromGroup(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['userId', 'channelId'])) {
            return;
        }

        $sender = $from->userData;
        $userId = $data['userId'];
        $channelId = $data['channelId'];

        try {
            $group = new \Group($this->logger);
            $groupId = $group->getGroupIdFromChannel($channelId);
            
            if (!$group->isGroupOwner($sender->id, $groupId)) {
                throw new \WebSocketException(
                    "Only the group owner can remove users from the group",
                    "NOT_GROUP_OWNER",
                    403
                );
            }
            
            if ($userId == $sender->id) {
                throw new \WebSocketException(
                    "Group owner cannot be removed from the group",
                    "CANNOT_REMOVE_OWNER",
                    400
                );
            }
            
            $group->checkUserInGroup($userId, $channelId);
            
            $this->processUserRemoval($from, $userId, $groupId, $channelId, $group, false);
            
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'remove_user_from_group');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'REMOVE_USER_FROM_GROUP_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Removing user from group failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'remove_user_from_group');
        }
    }
    
    private function processUserRemoval($from, $userId, $groupId, $channelId, $group, $isLeaving = false)
    {
        $sender = $from->userData;
        
        $group->removeUserFromGroup($userId, $channelId);
        
        if ($isLeaving) {
            $this->sendLeaveGroupResponse($from, $groupId, $channelId, $sender);
        } else {
            $this->sendRemoveUserResponse($from, $userId, $groupId, $channelId, $sender);
        }
    }
    
    private function sendLeaveGroupResponse($from, $groupId, $channelId, $sender)
    {
        ResponseHandlerService::sendSuccess($from, 'left_group', [
            'groupId' => $groupId,
            'channelId' => $channelId
        ]);
        
        $this->logger->info("User {$sender->id} left group {$groupId}");
        
        $group = new \Group($this->logger);
        $members = $group->getGroupMembers($channelId);
        
        $memberNotifyData = [
            'channelId' => $channelId,
            'groupId' => $groupId,
            'userLeft' => [
                'id' => $sender->id,
                'username' => $sender->username
            ]
        ];
        
        foreach ($members as $member) {
            $this->notificationService->notifyClient($member['userID'], 'user_left_group', $memberNotifyData);
        }
    }
    
    private function sendRemoveUserResponse($from, $userId, $groupId, $channelId, $sender)
    {
        ResponseHandlerService::sendSuccess($from, 'user_removed_from_group', [
            'userId' => $userId,
            'groupId' => $groupId,
            'channelId' => $channelId
        ]);
        
        $this->logger->info("User {$userId} removed from group {$groupId} by user {$sender->id}");
        
        $notifyData = [
            'channelId' => $channelId,
            'groupId' => $groupId,
            'removedBy' => [
                'id' => $sender->id,
                'username' => $sender->username
            ]
        ];
        
        $this->notificationService->notifyClient($userId, 'removed_from_group', $notifyData);
        
        $group = new \Group($this->logger);
        $members = $group->getGroupMembers($channelId, $sender->id);
        
        $memberNotifyData = [
            'channelId' => $channelId,
            'groupId' => $groupId,
            'removedUser' => [
                'id' => $userId
            ],
            'removedBy' => [
                'id' => $sender->id,
                'username' => $sender->username
            ]
        ];
        
        foreach ($members as $member) {
            $this->notificationService->notifyClient($member['userID'], 'user_removed_from_group', $memberNotifyData);
        }
    }
    
    public function handleUpdateGroupInfo(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['channelId'])) {
            return;
        }

        $sender = $from->userData;
        $channelId = $data['channelId'];
        $groupName = isset($data['groupName']) ? $data['groupName'] : null;
        $groupPicture = isset($data['groupPicture']) ? $data['groupPicture'] : null;

        if ($groupName === null && $groupPicture === null) {
            $this->errorHandler->sendError(
                $from,
                "At least one update parameter (groupName or groupPicture) must be provided",
                "MISSING_UPDATE_PARAMETERS",
                400
            );
            return;
        }

        try {
            $group = new \Group($this->logger);
            
            $groupId = $group->getGroupIdFromChannel($channelId);
            
            if (!$group->isGroupOwner($sender->id, $groupId)) {
                throw new \WebSocketException(
                    "Only the group owner can update group information",
                    "NOT_GROUP_OWNER",
                    403
                );
            }
            
            $channel = new \Channel();
            $result = $channel->updateGroupInfo($groupId, $groupName, $groupPicture);
            
            if (!$result) {
                throw new \ApiException("Failed to update group information", 500);
            }
            
            $groupInfo = $group->getGroupInfo($groupId);
            
            $this->sendGroupUpdateResponse($from, $groupId, $channelId, $groupName, $groupPicture, $groupInfo, $sender);
            
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'update_group_info');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'UPDATE_GROUP_INFO_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Updating group info failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'update_group_info');
        }
    }
    
    private function sendGroupUpdateResponse($from, $groupId, $channelId, $groupName, $groupPicture, $groupInfo, $sender)
    {
        $updateData = [
            'groupId' => $groupId,
            'channelId' => $channelId
        ];
        
        if ($groupName !== null) {
            $updateData['groupName'] = $groupName;
        }
        
        if ($groupPicture !== null) {
            $updateData['groupPicture'] = $groupPicture;
        }
        
        ResponseHandlerService::sendSuccess($from, 'group_info_updated', $updateData);
        
        $this->logger->info("Group info updated for group {$groupId} by user {$sender->id}");
        
        $group = new \Group($this->logger);
        $members = $group->getGroupMembers($channelId);
        
        $notifyData = [
            'channelId' => $channelId,
            'groupId' => $groupId,
            'updatedBy' => [
                'id' => $sender->id,
                'username' => $sender->username
            ]
        ];
        
        if ($groupName !== null) {
            $notifyData['groupName'] = $groupName;
        }
        
        if ($groupPicture !== null) {
            $notifyData['groupPicture'] = $groupPicture;
        }
        
        foreach ($members as $member) {
            if ($member['userID'] != $sender->id) {
                $this->notificationService->notifyClient($member['userID'], 'group_info_updated', $notifyData);
            }
        }
    }
    
    public function handleTransferOwnership(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['channelId', 'newOwnerId'])) {
            return;
        }

        $sender = $from->userData;
        $channelId = $data['channelId'];
        $newOwnerId = $data['newOwnerId'];

        try {
            $group = new \Group($this->logger);
            $groupId = $group->getGroupIdFromChannel($channelId);
            
            if (!$group->isGroupOwner($sender->id, $groupId)) {
                throw new \WebSocketException(
                    "Only the group owner can transfer ownership",
                    "NOT_GROUP_OWNER",
                    403
                );
            }
            
            $group->checkUserInGroup($newOwnerId, $channelId);
            
            $group->transferOwnership($groupId, $newOwnerId);
            
            $this->sendTransferOwnershipResponse($from, $groupId, $channelId, $newOwnerId, $sender);
            
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'transfer_ownership');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'TRANSFER_OWNERSHIP_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Transferring group ownership failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'transfer_ownership');
        }
    }
    
    private function sendTransferOwnershipResponse($from, $groupId, $channelId, $newOwnerId, $sender)
    {
        ResponseHandlerService::sendSuccess($from, 'ownership_transferred', [
            'groupId' => $groupId,
            'channelId' => $channelId,
            'newOwnerId' => $newOwnerId
        ]);
        
        $this->logger->info("Group {$groupId} ownership transferred from user {$sender->id} to user {$newOwnerId}");
        
        $user = new \User();
        $newOwnerInfo = $user->loadFromID($newOwnerId);
        $newOwnerUsername = $newOwnerInfo ? $newOwnerInfo['username'] : 'Unknown';
        
        $notifyData = [
            'channelId' => $channelId,
            'groupId' => $groupId,
            'previousOwner' => [
                'id' => $sender->id,
                'username' => $sender->username
            ]
        ];
        
        $this->notificationService->notifyClient($newOwnerId, 'group_ownership_received', $notifyData);
        
        $group = new \Group($this->logger);
        $members = $group->getGroupMembers($channelId);
        
        $memberNotifyData = [
            'channelId' => $channelId,
            'groupId' => $groupId,
            'previousOwner' => [
                'id' => $sender->id,
                'username' => $sender->username
            ],
            'newOwner' => [
                'id' => $newOwnerId,
                'username' => $newOwnerUsername
            ]
        ];
        
        foreach ($members as $member) {
            if ($member['userID'] != $sender->id && $member['userID'] != $newOwnerId) {
                $this->notificationService->notifyClient($member['userID'], 'group_ownership_transferred', $memberNotifyData);
            }
        }
    }
    
    public function handleDeleteGroup(ConnectionInterface $from, $data)
    {
        if (!$this->errorHandler->validateRequest($from, $data, ['channelId'])) {
            return;
        }

        $sender = $from->userData;
        $channelId = $data['channelId'];

        try {
            $group = new \Group($this->logger);
            $groupId = $group->getGroupIdFromChannel($channelId);
            
            if (!$group->isGroupOwner($sender->id, $groupId)) {
                throw new \WebSocketException(
                    "Only the group owner can delete the group",
                    "NOT_GROUP_OWNER",
                    403
                );
            }
            
            $members = $group->getGroupMembers($channelId);
            
            $group->deleteGroup($groupId, $channelId);
            
            $this->sendGroupDeletionResponse($from, $groupId, $channelId, $sender, $members);
            
        } catch (\WebSocketException $e) {
            $this->errorHandler->handleException($from, $e, 'delete_group');
        } catch (\ApiException $e) {
            $this->errorHandler->sendError(
                $from,
                $e->getMessage(),
                'DELETE_GROUP_FAILED',
                $e->getStatusCode()
            );
            $this->logger->error("Deleting group failed: {$e->getMessage()}");
        } catch (\Exception $e) {
            $this->errorHandler->handleException($from, $e, 'delete_group');
        }
    }
    
    private function sendGroupDeletionResponse($from, $groupId, $channelId, $sender, $members)
    {
        ResponseHandlerService::sendSuccess($from, 'group_deleted', [
            'groupId' => $groupId,
            'channelId' => $channelId
        ]);
        
        $this->logger->info("Group {$groupId} deleted by user {$sender->id}");
        
        $notifyData = [
            'channelId' => $channelId,
            'groupId' => $groupId,
            'deletedBy' => [
                'id' => $sender->id,
                'username' => $sender->username
            ]
        ];
        
        foreach ($members as $member) {
            if ($member['userID'] != $sender->id) {
                $this->notificationService->notifyClient($member['userID'], 'group_deleted', $notifyData);
            }
        }
    }
}