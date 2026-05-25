import React from "react";

const EditProfileMedia = ({

  profilePreview,
  coverPreview,

  profileData,

  handleProfilePhoto,
  handleCoverPhoto,

}) => {

  return (

    <div className="row g-4 mt-1">

      {/* PROFILE PHOTO */}

      <div className="col-md-6">

        <label className="form-label">
          Profile Photo
        </label>

        <input
          type="file"
          className="form-control"
          onChange={
            handleProfilePhoto
          }
        />

        {(profilePreview ||
          profileData.profile_photo) && (

          <img
            src={
              profilePreview ||
              profileData.profile_photo
            }
            className="img-fluid rounded mt-3"
            style={{
              width: "140px",
              height: "140px",
              objectFit: "cover",
            }}
          />

        )}

      </div>

      {/* COVER PHOTO */}

      <div className="col-md-6">

        <label className="form-label">
          Cover Photo
        </label>

        <input
          type="file"
          className="form-control"
          onChange={
            handleCoverPhoto
          }
        />

        {(coverPreview ||
          profileData.cover_photo) && (

          <img
            src={
              coverPreview ||
              profileData.cover_photo
            }
            className="img-fluid rounded mt-3"
            style={{
              width: "100%",
              height: "220px",
              objectFit: "cover",
            }}
          />

        )}

      </div>

    </div>

  );

};

export default EditProfileMedia;
