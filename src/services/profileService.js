import api from "./api";

/*
-----------------------------------
GET PROFILE
-----------------------------------
*/

export const getProfile = async (id) => {

  const response =
    await api.get(
      `member/${id}`
    );

  return response.data;

};

/*
-----------------------------------
UPDATE PROFILE
-----------------------------------
*/

export const updateProfile =
  async (formData) => {

    const response =
      await api.post(
        "update-profile",
        formData,
        {
          headers: {
            "Content-Type":
              "multipart/form-data",
          },
        }
      );

    return response.data;

  };
