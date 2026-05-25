import React, {
  useState,
} from "react";

import {
  registerUser,
} from "../services/authService";

import {
  Link,
  useNavigate,
} from "react-router-dom";

const Register = () => {

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

  const [username, setUsername] =
    useState("");

  const [gender, setGender] =
    useState("Man");

  const [email, setEmail] =
    useState("");

  const [password, setPassword] =
    useState("");

  const [showPassword, setShowPassword] =
    useState(false);

  const [loading, setLoading] =
    useState(false);

  const [error, setError] =
    useState("");

  /*
  -----------------------------------
  REGISTER
  -----------------------------------
  */

  const handleRegister =
    async (e) => {

      e.preventDefault();

      setLoading(true);

      setError("");

      try {

        /*
        -----------------------------------
        PAYLOAD
        -----------------------------------
        */

        const payload = {

          username,

          gender,

          email,

          password

        };

        /*
        -----------------------------------
        API
        -----------------------------------
        */

        const response =
        await registerUser(payload);

        console.log(response);

        /*
        -----------------------------------
        USER
        -----------------------------------
        */

        const user =
          response.user;

        /*
        -----------------------------------
        SAVE LOGIN
        -----------------------------------
        */

        localStorage.setItem(
          "spark_user",
          JSON.stringify(user)
        );

        /*
        -----------------------------------
        REDIRECT
        -----------------------------------
        */

        navigate(
          `/profile/${user.id}`
        );

      } catch (error) {

        console.log(error.response?.data);

        setError(

          error.response?.data?.data?.message ||

          error.response?.data?.message ||

          "Registration failed"

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

              Begin your story

            </h3>

            <p className="text-muted-2 small">

              Join the most curated community of romantics.

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
              handleRegister
            }
          >

            {/* USERNAME + GENDER */}

            <div className="row g-2">

              <div className="col-6">

                <label>

                  Username

                </label>

                <input
                  type="text"
                  className="form-control"
                  placeholder="Julian"
                  value={username}
                  onChange={(e) =>
                    setUsername(
                      e.target.value
                    )
                  }
                  required
                />

              </div>

              <div className="col-6">

                <label>

                  Gender

                </label>

                <select
                  className="form-control"
                  value={gender}
                  onChange={(e) =>
                    setGender(
                      e.target.value
                    )
                  }
                >

                  <option value="Man">

                    Man

                  </option>

                  <option value="Woman">

                    Woman

                  </option>

                </select>

              </div>

            </div>

            {/* EMAIL */}

            <label className="mt-3">

              Email

            </label>

            <input
              type="email"
              className="form-control"
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

            <label className="mt-3">

              Password

            </label>

            <div
              style={{
                position: "relative",
              }}
            >

              <input
                type={
                  showPassword
                    ? "text"
                    : "password"
                }
                className="form-control"
                placeholder="At least 8 characters"
                value={password}
                onChange={(e) =>
                  setPassword(
                    e.target.value
                  )
                }
                required
              />

              <span
                onClick={() =>
                  setShowPassword(
                    !showPassword
                  )
                }
                style={{
                  position: "absolute",
                  top: "50%",
                  right: "15px",
                  transform:
                    "translateY(-50%)",
                  cursor: "pointer",
                  zIndex: 99999,
                  width: "30px",
                  height: "30px",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                }}
              >

                <i
                  className={`bi ${
                    showPassword
                      ? "bi-eye-slash"
                      : "bi-eye"
                  }`}
                  style={{
                    fontSize: "18px",
                    color: "#999",
                    pointerEvents: "none",
                  }}
                ></i>

              </span>

            </div>

            {/* TERMS */}

            <div className="form-check mt-3">

              <input
                className="form-check-input"
                type="checkbox"
                id="tc"
                defaultChecked
              />

              <label
                className="form-check-label small text-muted-2"
                htmlFor="tc"
              >

                I agree to Spark's Terms & Community Guidelines.

              </label>

            </div>

            {/* SUBMIT */}

            <button
              type="submit"
              className="btn btn-spark w-100 glow mt-3"
              disabled={loading}
            >

              {loading
                ? "Creating..."
                : "Create Account"}

            </button>

            {/* LOGIN */}

            <p className="text-center mt-4 text-muted-2 small">

              Already a member?{" "}

              <Link
                to="/login"
                className="text-spark"
              >

                Sign in

              </Link>

            </p>

          </form>

        </div>

      </div>

    </div>

  );

};

export default Register;
