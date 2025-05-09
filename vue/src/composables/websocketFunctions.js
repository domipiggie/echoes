import { userdataStore } from '../store/UserdataStore';
import { useFriendshipStore } from '../store/FriendshipStore';
import { useChannelStore } from '../store/ChannelStore';
import { useMessageStore } from '../store/MessageStore';

import Message from '../classes/Message';
import User from '../classes/User';

const handleNewMessage = (data) => {
    const messageStore = useMessageStore();
    console.log(data.message.timestamp);
    if (data.message.channelId === messageStore.getCurrentChannelId) {
        const message = new Message(
            data.message.messageId,
            data.message.channelId,
            data.message.content,
            data.message.type,
            data.message.timestamp,
            new User(
                data.message.sender.id,
                data.message.sender.username
            )
        );
        messageStore.addMessage(message);
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

const handleGroupCreated = (data) => {
    if (isSuccess(data)) {
        var channelStore = useChannelStore();
        channelStore.fetchAllChannels();
    }
}

const isSuccess = (data) => {
    return data.status === "success";
}

export { handleNewMessage, handleDeleteMessage, isSuccess, handleOnFriendAdded, handleGroupCreated };