import { useState } from "react";

function useChat() {

  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState("");

  const sendMessage = () => {
    if (!input.trim()) return;

    const newMessage = {
      text: input,
      sender: "me",
    };

    setMessages([...messages, newMessage]);

    setInput("");
  };

  return {
    messages,
    input,
    setInput,
    sendMessage,
  };
}

export default useChat;