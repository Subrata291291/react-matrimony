import React, {
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
import {
  useLocation,
  useSearchParams,
} from "react-router-dom";
import MainLayout from "../components/layout/MainLayout";
import { useScrollReveal } from "../hooks";
import {
  getChatMessages,
  getChatUsers,
  sendChatMessage,
} from "../services/chatService";
import toast from "react-hot-toast";

const Messages = () => {
  useScrollReveal();
  const { state } =
    useLocation();
  const [searchParams] =
    useSearchParams();

  const [message, setMessage] =
    useState("");
  const [conversations, setConversations] =
    useState([]);
  const [activeUserId, setActiveUserId] =
    useState(null);
  const [chatMessages, setChatMessages] =
    useState([]);
  const [loadingUsers, setLoadingUsers] =
    useState(true);
  const [loadingMessages, setLoadingMessages] =
    useState(false);
  const [sending, setSending] =
    useState(false);
  const chatBodyRef = useRef(null);
  const requestedUserId =
    Number(
      searchParams.get("user")
    ) || null;
  const selectedChatUser =
    state?.chatUser
      ? {
          ...state.chatUser,
          id: Number(
            state.chatUser.id
          ),
        }
      : null;

  const activeConversation = useMemo(
    () =>
      conversations.find(
        (item) =>
          item.id === activeUserId
      ) || null,
    [conversations, activeUserId]
  );

  const mergeConversations = (
    chatUsers
  ) => {
    const merged =
      new Map();

    chatUsers.forEach((user) => {
      merged.set(user.id, user);
    });

    if (selectedChatUser?.id) {
      const existing =
        merged.get(
          selectedChatUser.id
        ) || {};

      merged.set(
        selectedChatUser.id,
        {
          ...selectedChatUser,
          ...existing,
          id: selectedChatUser.id,
          name:
            selectedChatUser.name ||
            existing.name ||
            "Member",
          image:
            selectedChatUser
              .profile_image ||
            selectedChatUser
              .photo ||
            existing.image ||
            "",
          status:
            existing.status ||
            (selectedChatUser.is_online
              ? "Online"
              : selectedChatUser.last_seen
              ? `Last seen ${selectedChatUser.last_seen}`
              : "Recently active"),
          city:
            selectedChatUser.city ||
            existing.city ||
            "",
          profession:
            selectedChatUser.profession ||
            existing.profession ||
            "",
          message:
            existing.message || "",
          unreadCount:
            existing.unreadCount ||
            0,
        }
      );
    }

    return Array.from(
      merged.values()
    );
  };

  useEffect(() => {
    loadConversations();
  }, []);

  useEffect(() => {
    if (!activeUserId) {
      return;
    }

    loadChatMessages(activeUserId);

    const intervalId =
      window.setInterval(() => {
        loadChatMessages(
          activeUserId,
          false
        );
        loadConversations(false);
      }, 5000);

    return () =>
      window.clearInterval(intervalId);
  }, [activeUserId]);

  useEffect(() => {
    if (chatBodyRef.current) {
      chatBodyRef.current.scrollTop =
        chatBodyRef.current.scrollHeight;
    }
  }, [chatMessages]);

  const loadConversations =
    async (
    showLoader = true
  ) => {
    if (showLoader) {
      setLoadingUsers(true);
    }

    try {
      let chatUsers = [];

      try {
        const data =
          await getChatUsers();
        chatUsers =
          data.users || [];
      } catch (error) {
        if (!selectedChatUser?.id) {
          throw error;
        }
      }

      const users =
        mergeConversations(
          chatUsers
        );

      setConversations(users);

      if (
        requestedUserId &&
        users.some(
          (item) =>
            item.id ===
            requestedUserId
        )
      ) {
        setActiveUserId(
          requestedUserId
        );
        return;
      }

      if (
        selectedChatUser?.id &&
        users.some(
          (item) =>
            item.id ===
            selectedChatUser.id
        )
      ) {
        setActiveUserId(
          selectedChatUser.id
        );
        return;
      }

      if (
        !activeUserId &&
        users.length
      ) {
        setActiveUserId(
          users[0].id
        );
      }
    } catch (error) {
      if (showLoader) {
        toast.error(
          error.response?.data?.message ||
          "Failed to load chats"
        );
      }
    } finally {
      if (showLoader) {
        setLoadingUsers(false);
      }
    }
  };

  const loadChatMessages = async (
    userId,
    showLoader = true
  ) => {
    if (showLoader) {
      setLoadingMessages(true);
    }

    try {
      const data =
        await getChatMessages(userId);

      setChatMessages(
        data.messages || []
      );
    } catch (error) {
      if (showLoader) {
        toast.error(
          error.response?.data?.message ||
          "Failed to load messages"
        );
      }
    } finally {
      if (showLoader) {
        setLoadingMessages(false);
      }
    }
  };

  const handleSendMessage =
    async () => {
      if (
        !message.trim() ||
        !activeUserId ||
        sending
      ) {
        return;
      }

      setSending(true);

      try {
        await sendChatMessage({
          receiverId: activeUserId,
          message: message.trim(),
        });

        setMessage("");

        await Promise.all([
          loadChatMessages(
            activeUserId,
            false
          ),
          loadConversations(
            false
          ),
        ]);
      } catch (error) {
        toast.error(
          error.response?.data?.message ||
          "Failed to send message"
        );
      } finally {
        setSending(false);
      }
    };

  return (
    <MainLayout>
      <main
        className="container"
        style={{
          paddingTop: "110px",
          paddingBottom: "60px",
        }}
      >
        <div className="messages-wrap reveal">
          <div className="convo-list">
            <div className="chat-header">
              <h5 className="m-0">
                <i
                  className="bi bi-chat-heart-fill"
                  style={{
                    color:
                      "var(--accent)",
                  }}
                ></i>{" "}
                Messages
              </h5>
            </div>

            {loadingUsers ? (
              <div className="p-4 text-muted-2">
                Loading chats...
              </div>
            ) : conversations.length ? (
              conversations.map((item) => (
                <button
                  key={item.id}
                  type="button"
                  className={`convo-item ${
                    item.id ===
                    activeUserId
                      ? "active"
                      : ""
                  }`}
                  onClick={() =>
                    setActiveUserId(
                      item.id
                    )
                  }
                  style={{
                    background:
                      "transparent",
                    border: "none",
                    textAlign: "left",
                    width: "100%",
                  }}
                >
                  <img
                    className="avatar"
                    src={item.image}
                    alt={item.name}
                  />

                  <div className="flex-grow-1">
                    <strong>
                      {item.name}
                    </strong>

                    <div className="small text-muted-2">
                      {item.message ||
                        item.city ||
                        "Start chatting"}
                    </div>
                  </div>

                  {!!item.unreadCount && (
                    <span className="chip active">
                      {item.unreadCount}
                    </span>
                  )}
                </button>
              ))
            ) : (
              <div className="p-4 text-muted-2">
                No conversations yet.
              </div>
            )}
          </div>

          <div className="chat-panel">
            <div className="chat-header">
              {activeConversation ? (
                <>
                  <img
                    className="avatar"
                    src={
                      activeConversation.image
                    }
                    alt={
                      activeConversation.name
                    }
                  />

                  <div className="flex-grow-1">
                    <strong>
                      {
                        activeConversation.name
                      }
                    </strong>

                    <div className="small text-muted-2">
                      {activeConversation.status ||
                        "Recently active"}
                    </div>
                  </div>
                </>
              ) : (
                <div className="small text-muted-2">
                  Select a conversation
                </div>
              )}
            </div>

            <div
              ref={chatBodyRef}
              className="chat-body"
            >
              {loadingMessages ? (
                <div className="text-center small text-muted-2">
                  Loading conversation...
                </div>
              ) : chatMessages.length ? (
                chatMessages.map((msg) => (
                  <div
                    key={msg.id}
                    className={`bubble ${
                      msg.type
                    }`}
                    title={msg.time}
                  >
                    {msg.text}
                  </div>
                ))
              ) : (
                <div className="text-center small text-muted-2">
                  No messages yet.
                </div>
              )}
            </div>

            <div className="chat-input">
              <input
                placeholder="Type your message..."
                value={message}
                onChange={(e) =>
                  setMessage(
                    e.target.value
                  )
                }
                onKeyDown={(e) => {
                  if (
                    e.key === "Enter"
                  ) {
                    e.preventDefault();
                    handleSendMessage();
                  }
                }}
                disabled={!activeUserId}
              />

              <button
                className="btn btn-spark"
                onClick={
                  handleSendMessage
                }
                disabled={
                  !activeUserId ||
                  sending
                }
              >
                <i className="bi bi-send-fill"></i>
              </button>
            </div>
          </div>

          <div
            className="insight-panel"
            style={{ padding: "20px" }}
          >
            <h5 className="mb-3">
              Conversation
            </h5>

            {activeConversation ? (
              <>
                <div className="mb-3">
                  <img
                    src={
                      activeConversation.image
                    }
                    alt={
                      activeConversation.name
                    }
                    style={{
                      width: "100%",
                      borderRadius: "18px",
                      aspectRatio: "1 / 1",
                      objectFit: "cover",
                    }}
                  />
                </div>

                <div className="card-spark mb-3">
                  <div className="small text-muted-2">
                    Name
                  </div>
                  <strong>
                    {activeConversation.name}
                  </strong>
                </div>

                <div className="card-spark mb-3">
                  <div className="small text-muted-2">
                    Status
                  </div>
                  <strong>
                    {activeConversation.status}
                  </strong>
                </div>

                <div className="card-spark">
                  <div className="small text-muted-2">
                    Last message
                  </div>
                  <small>
                    {activeConversation.message ||
                      activeConversation.city ||
                      "No messages yet."}
                  </small>
                </div>
              </>
            ) : (
              <div className="text-muted-2 small">
                Choose a chat to see details.
              </div>
            )}
          </div>
        </div>
      </main>
    </MainLayout>
  );
};

export default Messages;
