import React from "react";
import { Link } from "react-router-dom";

const Footer = () => {
  return (
    <footer className="footer">
      <div className="container d-flex justify-content-between flex-wrap gap-3">
        {/* Brand */}
        <span className="brand">✦ Spark</span>

        {/* Footer Links */}
        <div>
          <Link
            to="/safety"
            className="me-3"
          >
            Safety
          </Link>

          <Link
            to="/community"
            className="me-3"
          >
            Community
          </Link>

          <Link
            to="/terms"
            className="me-3"
          >
            Terms
          </Link>

          <Link
            to="/privacy"
            className="me-3"
          >
            Privacy
          </Link>

          <Link to="/support">
            Support
          </Link>
        </div>

        {/* Copyright */}
        <span>
          © 2026 Spark. All rights reserved.
        </span>
      </div>
    </footer>
  );
};

export default Footer;