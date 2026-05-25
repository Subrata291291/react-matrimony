import { useState } from "react";

function ChatWindow() {

  const [messages, setMessages] = useState([]);
  const [input, setInput] = useState("");

  const sendMessage = (e) => {
    if (e.key === "Enter" && input.trim()) {
      setMessages([
        ...messages,
        {
          text: input,
          sender: "me",
        },
      ]);

      setInput("");
    }
  };

  return (
    <div className="chat-body">

      {messages.map((msg, index) => (
        <div
          key={index}
          className={`bubble ${msg.sender}`}
        >
          {msg.text}
        </div>
      ))}

      <input
        type="text"
        value={input}
        onChange={(e) => setInput(e.target.value)}
        onKeyDown={sendMessage}
        placeholder="Type message..."
      />
    </div>
  );
}

export default ChatWindow;