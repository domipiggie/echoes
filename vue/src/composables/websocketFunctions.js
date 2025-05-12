import { userdataStore } from '../store/UserdataStore';
import { useFriendshipStore } from '../store/FriendshipStore';
import { useChannelStore } from '../store/ChannelStore';
import { useMessageStore } from '../store/MessageStore';

import Message from '../classes/Message';
import User from '../classes/User';

const handleNewMessage = (data) => {
    const messageStore = useMessageStore();
    const channelStore = useChannelStore();
    console.log(data.message.timestamp);
    if (data.message.channelId === messageStore.getCurrentChannelId) {
        const message = new Message(
            data.message.messageId,
            data.message.channelId,
            data.message.content,
            data.message.messageType,
            data.message.timestamp,
            new User(
                data.message.sender.id,
                data.message.sender.username,
                data.message.sender.username,
                data.message.sender.profilePicture
            ),
            data.message.replyTo
        );
        messageStore.addMessage(message);
        channelStore.updateLastMessage(data.message.sender.username, data.message.content, data.message.channelId);
    }
};

const handleDeleteMessage = (data) => {
    const messageStore = useMessageStore();
    if (data.message.channelId === messageStore.getCurrentChannelId) {
        messageStore.removeMessage(data.message.messageId);
    }
}

const handleOnFriendAdded = (data) => {
    if (isSuccess(data)) {
        var friendshipStore = useFriendshipStore();
        friendshipStore.clearFriendships();
        friendshipStore.fetchFriendships();
    }
}

const handleOnFriendChange = () => {
    var friendshipStore = useFriendshipStore();
    var channelStore = useChannelStore();
    friendshipStore.clearFriendships();
    channelStore.clearChannels();
    friendshipStore.fetchFriendships();
    channelStore.fetchAllChannels();
}

const handleFriendRemove = (data) => {
    var messageStore = useMessageStore();
    messageStore.clearMessages();
    messageStore.setCurrentChannelId(null);
    messageStore.setCurrentChannelName('');

    handleOnFriendChange();
}

const handleFriendRemoved = (data) => {
    const messageStore = useMessageStore();
    const channelStore = useChannelStore();
    const currentChannel = channelStore.getFriendChannelById(messageStore.getCurrentChannelId);

    if (currentChannel && (currentChannel.getUser1().getUserID() == data.sender.id || currentChannel.getUser2().getUserID() == data.sender.id)) {
        handleFriendRemove();
    } else {
        handleOnFriendChange();
    }
}

const onGroupChange = (data) => {
    const channelStore = useChannelStore();
    channelStore.fetchGroupChannels();

    if (data.channelId && data.groupName) {
        const messageStore = useMessageStore();
        if (data.channelId == messageStore.getCurrentChannelId) {
            messageStore.setCurrentChannelName(data.groupName);
        }
    }
}

const onRemovedFromGroup = (data) => {
    const messageStore = useMessageStore();

    if (data.channelId == messageStore.getCurrentChannelId) {
        messageStore.setCurrentChannelId(null);
        messageStore.setCurrentChannelName('');
        messageStore.clearMessages();
    }

    onGroupChange();
}

const handleGroupCreated = (data) => {
    if (isSuccess(data)) {
        var channelStore = useChannelStore();
        channelStore.fetchAllChannels();
    }
}

const isSuccess = (data) => {
    return data.status === "success";
}

const handleMessageUpdate = (data) => {
    const messageStore = useMessageStore();
    if (data.message.channelId === messageStore.getCurrentChannelId) {
        messageStore.updateWebSocketMessage(data.message.messageId, data.message.content);
        console.log('Üzenet frissítve a WebSocket-en keresztül:', data.message);
    }
}

const handleGroupDeleted = (data) => {
    const channelStore = useChannelStore();
    const messageStore = useMessageStore();
    
    if (data.channelId === messageStore.getCurrentChannelId) {
        messageStore.setCurrentChannelId(null);
        messageStore.setCurrentChannelName('');
        messageStore.clearMessages();
    }
    
    channelStore.fetchAllChannels();
}

export { onGroupChange, onRemovedFromGroup, handleOnFriendChange, handleFriendRemoved, handleFriendRemove, handleNewMessage, handleDeleteMessage, isSuccess, handleOnFriendAdded, handleGroupCreated, handleMessageUpdate, handleGroupDeleted };