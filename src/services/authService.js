import axios from "axios";

/*
===================================
API BASE
===================================
*/

const API_URL =
  "https://store.zyraluxe.in/wp-json/wpm/v1";

const AJAX_URL =
  `${window.location.origin}/wp-admin/admin-ajax.php`;

const getAjaxNonce = () =>
  window.wpm_ajax?.nonce || "";

const canUseAjaxLogin = () =>
  Boolean(getAjaxNonce());

/*
===================================
REGISTER
===================================
*/

export const registerUser = async (
  userData
) => {
  const payload = {
    name:
      userData.username,
    email:
      userData.email,
    password:
      userData.password,

    age: 25,

    looking_for:
      userData.gender === "Man"
        ? "Woman"
        : "Man"
  };

  const response =
    await axios.post(

      `${API_URL}/register`,

      payload,

      {
        headers: {
          "Content-Type":
            "application/json",
        },
      }

    );

  return response.data;

};

/*
===================================
LOGIN
===================================
*/

export const loginUser = async (
  userData
) => {
  const response =
    await axios.post(
      `${API_URL}/login`,
      {
        username:
          userData.email,
        password:
          userData.password,
      },
      {
        headers: {
          "Content-Type":
            "application/json",
        },
        withCredentials: true,
      }
    );

  if (canUseAjaxLogin()) {
    const formData =
      new FormData();

    formData.append(
      "action",
      "wpm_login_user"
    );
    formData.append(
      "security",
      getAjaxNonce()
    );
    formData.append(
      "username",
      userData.email
    );
    formData.append(
      "password",
      userData.password
    );

    await axios.post(
      AJAX_URL,
      formData,
      {
        withCredentials: true,
      }
    );
  }

  return response.data;

};
