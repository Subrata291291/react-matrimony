import React from "react";

const EditProfileAbout = ({
  profileData,
  handleChange,
}) => {

  return (

    <div className="mt-4">

      {/* ABOUT ME */}

      <div className="mb-4">

        <label className="form-label">
          About Me
        </label>

        <textarea
          name="about"
          value={profileData.about}
          onChange={handleChange}
          className="form-control"
          rows="6"
          placeholder="Write something about yourself..."
        />

      </div>

      {/* HOBBIES */}

      <div className="mb-4">

        <label className="form-label">
          Hobbies
        </label>

        <textarea
          name="hobbies"
          value={profileData.hobbies}
          onChange={handleChange}
          className="form-control"
          rows="5"
          placeholder="Music, travel, photography..."
        />

      </div>

      {/* PARTNER EXPECTATIONS */}

      <div className="mb-4">

        <label className="form-label">
          Partner Expectations
        </label>

        <textarea
          name="partner_expectations"
          value={
            profileData.partner_expectations || ""
          }
          onChange={handleChange}
          className="form-control"
          rows="5"
          placeholder="Describe your ideal partner..."
        />

      </div>

    </div>

  );

};

export default EditProfileAbout;