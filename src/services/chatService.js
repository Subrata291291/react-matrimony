import axios from "axios";

const WP_BASE_URL =
  window.location.origin ||
  "https://store.zyraluxe.in";

const REST_API_URL =
  `${WP_BASE_URL}/wp-json/wpm/v1`;

const CHAT_AJAX_URL =
  `${WP_BASE_URL}/wp-admin/admin-ajax.php`;

const api = axios.create({
  withCredentials: true,
});

const getAjaxNonce = () =>
  window.wpm_ajax?.nonce || "";

const getRestNonce = () =>
  window.wpApiSettings?.nonce ||
  window.wpApiSettings?.rest_nonce ||
  window.wpm_ajax?.rest_nonce ||
  "";

const createFormData = (
  fields
) => {
  const formData =
    new FormData();

  Object.entries(fields).forEach(
    ([key, value]) => {
      formData.append(key, value);
    }
  );

  return formData;
};

const parseHtml = (html) => {
  const parser =
    new DOMParser();

  return parser.parseFromString(
    html,
    "text/html"
  );
};

const createAxiosStyleError = (
  message
) => ({
  response: {
    data: { message },
  },
});

const normalizeChatUser = (
  user
) => ({
  id: Number(user.id),
  name:
    user.name || "Member",
  message:
    user.message || "",
  image:
    user.image ||
    user.photo ||
    user.profile_image ||
    "",
  status:
    user.status ||
    (user.is_online
      ? "Online"
      : user.last_seen
      ? `Last seen ${user.last_seen}`
      : "Recently active"),
  unreadCount: Number(
    user.unreadCount || 0
  ),
  isOnline: Boolean(
    user.isOnline ||
      user.is_online
  ),
  city: user.city || "",
  profession:
    user.profession || "",
});

const normalizeMessage = (
  message
) => ({
  id: Number(message.id),
  type:
    message.type === "me" ||
    message.type === "sent"
      ? "me"
      : "them",
  text:
    message.text ||
    message.message ||
    "",
  time:
    message.time || "",
});

const canUseAjaxFallback =
  () => Boolean(getAjaxNonce());

const getRestConfig = () => {
  const restNonce =
    getRestNonce();

  return restNonce
    ? {
        headers: {
          "X-WP-Nonce":
            restNonce,
        },
      }
    : {};
};

const parseAjaxUsers = (
  html
) => {
  const doc = parseHtml(
    html || ""
  );

  return Array.from(
    doc.querySelectorAll(
      ".wpm-chat-user"
    )
  ).map((node) =>
    normalizeChatUser({
      id: node.dataset.userId,
      name:
        node.querySelector("h5")
          ?.textContent?.trim(),
      message:
        node.querySelector("p")
          ?.textContent?.trim(),
      image:
        node.querySelector("img")
          ?.getAttribute("src"),
      status:
        node.dataset.status,
      unreadCount:
        node.querySelector(
          ".wpm-unread-count"
        )?.textContent || 0,
      isOnline: Boolean(
        node.querySelector(
          ".wpm-online-dot"
        )
      ),
    })
  );
};

const parseAjaxMessages = (
  html
) => {
  const doc = parseHtml(
    html || ""
  );

  return Array.from(
    doc.querySelectorAll(
      ".wpm-chat-message"
    )
  ).map((node) =>
    normalizeMessage({
      id: node.dataset.messageId,
      type: node.classList.contains(
        "sent"
      )
        ? "me"
        : "them",
      text:
        node.querySelector(
          ".wpm-chat-text"
        )?.textContent?.trim(),
      time:
        node.querySelector(
          ".wpm-chat-time"
        )?.textContent?.trim(),
    })
  );
};

export const getChatUsers =
  async () => {
    if (canUseAjaxFallback()) {
      const response =
        await api.post(
          CHAT_AJAX_URL,
          createFormData({
            action:
              "wpm_load_chat_users",
            security:
              getAjaxNonce(),
          })
        );

      if (
        typeof response.data ===
          "object" &&
        response.data?.success ===
          false
      ) {
        throw createAxiosStyleError(
          response.data?.data
            ?.message ||
            "Unable to load chats."
        );
      }

      return {
        users: parseAjaxUsers(
          response.data
        ),
      };
    }

    const response =
      await api.get(
        `${REST_API_URL}/chat/users`,
        getRestConfig()
      );

    return {
      users: (
        response.data?.users || []
      ).map(normalizeChatUser),
    };
  };

export const getChatMessages =
  async (userId) => {
    if (canUseAjaxFallback()) {
      const response =
        await api.post(
          CHAT_AJAX_URL,
          createFormData({
            action:
              "wpm_load_chat",
            security:
              getAjaxNonce(),
            user_id: userId,
            mark_read: 1,
          })
        );

      if (
        typeof response.data ===
          "object" &&
        response.data?.success ===
          false
      ) {
        throw createAxiosStyleError(
          response.data?.data
            ?.message ||
            "Unable to load your chat."
        );
      }

      return {
        messages:
          parseAjaxMessages(
            response.data
          ),
      };
    }

    const response =
      await api.get(
        `${REST_API_URL}/chat/messages/${userId}`,
        getRestConfig()
      );

    return {
      messages: (
        response.data
          ?.messages || []
      ).map(
        normalizeMessage
      ),
    };
  };

export const sendChatMessage =
  async ({
    receiverId,
    message,
  }) => {
    if (canUseAjaxFallback()) {
      const response =
        await api.post(
          CHAT_AJAX_URL,
          createFormData({
            action:
              "wpm_send_message",
            security:
              getAjaxNonce(),
            receiver_id:
              receiverId,
            message,
          })
        );

      if (!response.data?.success) {
        throw createAxiosStyleError(
          response.data?.data
            ?.message ||
            "Unable to send your message."
        );
      }

      return response.data;
    }

    try {
      const response =
        await api.post(
          `${REST_API_URL}/chat/send`,
          {
            receiver_id:
              receiverId,
            message,
          },
          getRestConfig()
        );

      if (
        typeof response.data ===
          "object" &&
        response.data?.success ===
          false
      ) {
        throw createAxiosStyleError(
          response.data?.data
            ?.message ||
            "Unable to send your message."
        );
      }

      return response.data;
    } catch (error) {
      if (
        error.response
      ) {
        throw error;
      }

      throw createAxiosStyleError(
        error.message ||
        "Unable to send your message."
      );
    }
  };
