import React, {
  useState,
} from "react";

import {
  loginUser,
} from "../services/authService";

import {
  Link,
  useNavigate,
} from "react-router-dom";

const Login = () => {

  /*
  -----------------------------------
  NAVIGATE
  -----------------------------------
  */

  const navigate =
    useNavigate();

  /*
  -----------------------------------
  STATES
  -----------------------------------
  */

  const [email, setEmail] =
    useState("");

  const [password, setPassword] =
    useState("");

  const [loading, setLoading] =
    useState(false);

  const [error, setError] =
    useState("");

  /*
  -----------------------------------
  LOGIN
  -----------------------------------
  */

const handleLogin =
  async (e) => {

    e.preventDefault();

    setLoading(true);

    setError("");

    try {

      const response =
        await loginUser({

          email,
          password,

        });

      /*
      USER DATA
      */

      const user =
        response.user;

      /*
      SAVE USER
      */

      localStorage.setItem(

        "spark_user",

        JSON.stringify(user)

      );

      /*
      REDIRECT
      */

      navigate(

        `/profile/${user.id}`

      );

    } catch (err) {

      console.log(err);

      setError(
        err.response?.data?.data?.message ||
        err.response?.data?.message ||
        "Invalid email or password"
      );

    } finally {

      setLoading(false);

    }

  };

  return (

    <div className="auth-wrap">

      <div
        className="auth-card tilt-3d"
        data-tilt
      >

        <div className="tilt-inner">

          {/* HEADER */}

          <div className="text-center mb-4">

            <Link
              to="/"
              className="brand"
              style={{
                fontSize: "2rem",
              }}
            >

              ✦ Spark

            </Link>

            <h3 className="mt-2">

              Welcome back

            </h3>

            <p className="text-muted-2 small">

              Sign in to rekindle your spark.

            </p>

          </div>

          {/* ERROR */}

          {error && (

            <div className="alert alert-danger">

              {error}

            </div>

          )}

          {/* FORM */}

          <form
            onSubmit={
              handleLogin
            }
          >

            {/* EMAIL */}

            <label>
              Email
            </label>

            <input
              type="email"
              className="form-control mb-3"
              placeholder="you@spark.love"
              value={email}
              onChange={(e) =>
                setEmail(
                  e.target.value
                )
              }
              required
            />

            {/* PASSWORD */}

            <label>
              Password
            </label>

            <input
              type="password"
              className="form-control mb-3"
              placeholder="••••••••"
              value={password}
              onChange={(e) =>
                setPassword(
                  e.target.value
                )
              }
              required
            />

            {/* REMEMBER */}

            <div className="d-flex justify-content-between small mb-3">

              <label className="text-muted-2">

                <input
                  type="checkbox"
                  className="me-1"
                />

                Remember me

              </label>

              <a href="/forgot-password">

                Forgot password?

              </a>

            </div>

            {/* SUBMIT */}

            <button
              type="submit"
              className="btn btn-spark w-100 glow"
              disabled={loading}
            >

              {loading
                ? "Signing In..."
                : "Sign In"}

            </button>

            {/* DIVIDER */}

            <div className="text-center my-3 text-muted-2 small">

              — or continue with —

            </div>

            {/* SOCIAL */}

            <div className="d-flex gap-2">

              <button
                type="button"
                className="btn btn-ghost w-100"
              >

                <i className="bi bi-google"></i>

                {" "}

                Google

              </button>

              <button
                type="button"
                className="btn btn-ghost w-100"
              >

                <i className="bi bi-apple"></i>

                {" "}

                Apple

              </button>

            </div>

            {/* REGISTER */}

            <p className="text-center mt-4 text-muted-2 small">

              New here?{" "}

              <Link
                to="/register"
                className="text-spark"
              >

                Create an account

              </Link>

            </p>

          </form>

        </div>

      </div>

    </div>

  );

};

export default Login;
