import React from "react";

import {
  Link,
  NavLink,
} from "react-router-dom";

const Navbar = () => {

  /*
  -----------------------------------
  GET LOGGED IN USER
  -----------------------------------
  */

  const loggedInUser =
    JSON.parse(
      localStorage.getItem(
        "spark_user"
      )
    );

  return (

    <nav className="navbar navbar-expand-md navbar-spark fixed-top">

      <div className="container">

        {/* LOGO */}

        <Link
          className="brand"
          to="/"
        >

          ✦ Spark

        </Link>

        {/* MOBILE TOGGLE */}

        <button
          className="navbar-toggler text-light border-0"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#nav"
          aria-controls="nav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >

          <i className="bi bi-list fs-3"></i>

        </button>

        {/* MENU */}

        <div
          className="collapse navbar-collapse justify-content-md-end"
          id="nav"
        >

          {/* HOME */}

          <NavLink
            to="/"
            className={({ isActive }) =>
              `nav-link ${
                isActive
                  ? "active"
                  : ""
              }`
            }
          >

            Home

          </NavLink>

          {/* DISCOVER */}

          <NavLink
            to="/discover"
            className={({ isActive }) =>
              `nav-link ${
                isActive
                  ? "active"
                  : ""
              }`
            }
          >

            Discover

          </NavLink>

          {/* MESSAGES */}

          <NavLink
            to="/messages"
            className={({ isActive }) =>
              `nav-link ${
                isActive
                  ? "active"
                  : ""
              }`
            }
          >

            Messages

          </NavLink>

          {/* PROFILE */}

          <NavLink
            to={
              loggedInUser
                ? `/profile/${loggedInUser.id}`
                : "/login"
            }
            className={({ isActive }) =>
              `nav-link ${
                isActive
                  ? "active"
                  : ""
              }`
            }
          >

            Profile

          </NavLink>

          {/* LOGIN / USER */}

          {loggedInUser ? (

            <Link
              to={`/profile/${loggedInUser.id}`}
              className="btn btn-spark ms-3"
            >

              {
                loggedInUser.name
              }

            </Link>

          ) : (

            <Link
              to="/register"
              className="btn btn-spark ms-3"
            >

              Join Spark

            </Link>

          )}

        </div>

      </div>

    </nav>

  );

};

export default Navbar;