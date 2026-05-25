import React from "react";
import ReactDOM from "react-dom/client";

import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap/dist/js/bootstrap.bundle.min.js";
import "bootstrap-icons/font/bootstrap-icons.css";

import { Toaster } from "react-hot-toast";

import "./styles/style.css";

import App from "./App";

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <App />
    <Toaster
      position="top-center"
      toastOptions={{
        style: {
          background: "#1e1528",
          color: "#fff",
          border: "1px solid #8b5cf6",
          borderRadius: "14px",
          padding: "14px 18px",
          fontSize: "14px",
          marginTop: "45vh",
        },

        success: {
          iconTheme: {
            primary: "#a855f7",
            secondary: "#fff",
          },
        },

        error: {
          iconTheme: {
            primary: "#ef4444",
            secondary: "#fff",
          },
        },
      }}
    />
  </React.StrictMode>
);