import React from "react";

const EditProfileBasic = ({
  profileData,
  handleChange,
}) => {

  return (

    <div className="row g-4">

      {/* FULL NAME */}

      <div className="col-md-6">

        <label className="form-label">
          Full Name
        </label>

        <input
          type="text"
          name="full_name"
          value={profileData.full_name}
          onChange={handleChange}
          className="form-control"
        />

      </div>

      {/* GENDER */}

      <div className="col-md-6">

        <label className="form-label">
          Gender
        </label>

        <select
          name="gender"
          value={profileData.gender}
          onChange={handleChange}
          className="form-select"
        >

          <option value="">
            Select Gender
          </option>

          <option value="Man">
            Man
          </option>

          <option value="Woman">
            Woman
          </option>

        </select>

      </div>

      {/* LOOKING FOR */}

      <div className="col-md-6">

        <label className="form-label">
          Looking For
        </label>

        <select
          name="looking_for"
          value={profileData.looking_for}
          onChange={handleChange}
          className="form-select"
        >

          <option value="">
            Select Preference
          </option>

          <option value="Man">
            Man
          </option>

          <option value="Woman">
            Woman
          </option>

        </select>

      </div>

      {/* DOB */}

      <div className="col-md-6">

        <label className="form-label">
          Date of Birth
        </label>

        <input
          type="date"
          name="dob"
          value={profileData.dob}
          onChange={handleChange}
          className="form-control"
        />

      </div>

      {/* RELIGION */}

      <div className="col-md-6">

        <label className="form-label">
          Religion
        </label>

        <select
          name="religion"
          value={profileData.religion}
          onChange={handleChange}
          className="form-select"
        >

          <option value="">
            Select Religion
          </option>

          <option value="Hindu">
            Hindu
          </option>

          <option value="Muslim">
            Muslim
          </option>

          <option value="Christian">
            Christian
          </option>

          <option value="Sikh">
            Sikh
          </option>

          <option value="Buddhist">
            Buddhist
          </option>

          <option value="Jain">
            Jain
          </option>

          <option value="Other">
            Other
          </option>

        </select>

      </div>

      {/* PROFESSION */}

      <div className="col-md-6">

        <label className="form-label">
          Profession
        </label>

        <select
          name="profession"
          value={profileData.profession}
          onChange={handleChange}
          className="form-select"
        >

          <option value="">
            Select Profession
          </option>

          <option value="Software Engineer">
            Software Engineer
          </option>

          <option value="Doctor">
            Doctor
          </option>

          <option value="Teacher">
            Teacher
          </option>

          <option value="Business">
            Business
          </option>

          <option value="Designer">
            Designer
          </option>

          <option value="Government Job">
            Government Job
          </option>

          <option value="Lawyer">
            Lawyer
          </option>

          <option value="Student">
            Student
          </option>

          <option value="Makeup Artist">
            Makeup Artist
          </option>

          <option value="Photographer">
            Photographer
          </option>

          <option value="Marketing">
            Marketing
          </option>

          <option value="Other">
            Other
          </option>

        </select>

      </div>

      {/* EDUCATION */}

      <div className="col-md-6">

        <label className="form-label">
          Education
        </label>

        <select
          name="education"
          value={profileData.education}
          onChange={handleChange}
          className="form-select"
        >

          <option value="">
            Select Education
          </option>

          <option value="High School">
            High School
          </option>

          <option value="Secondary School">
            Secondary School
          </option>

          <option value="Higher Secondary">
            Higher Secondary
          </option>

          <option value="Diploma">
            Diploma
          </option>

          <option value="ITI">
            ITI
          </option>

          <option value="Bachelor of Arts (BA)">
            Bachelor of Arts (BA)
          </option>

          <option value="Bachelor of Science (BSc)">
            Bachelor of Science (BSc)
          </option>

          <option value="Bachelor of Commerce (BCom)">
            Bachelor of Commerce (BCom)
          </option>

          <option value="Bachelor of Technology (BTech)">
            Bachelor of Technology (BTech)
          </option>

          <option value="Bachelor of Engineering (BE)">
            Bachelor of Engineering (BE)
          </option>

          <option value="Bachelor of Computer Applications (BCA)">
            Bachelor of Computer Applications (BCA)
          </option>

          <option value="Bachelor of Business Administration (BBA)">
            Bachelor of Business Administration (BBA)
          </option>

          <option value="MBBS">
            MBBS
          </option>

          <option value="BDS">
            BDS
          </option>

          <option value="BPharm">
            BPharm
          </option>

          <option value="MA">
            Master of Arts (MA)
          </option>

          <option value="MSc">
            Master of Science (MSc)
          </option>

          <option value="MBA">
            MBA
          </option>

          <option value="MCA">
            MCA
          </option>

          <option value="PhD">
            PhD
          </option>

        </select>

      </div>

      {/* COUNTRY */}

      <div className="col-md-6">

        <label className="form-label">
          Country
        </label>

        <input
          type="text"
          name="country"
          value={profileData.country}
          onChange={handleChange}
          className="form-control"
        />

      </div>

      {/* STATE */}

      <div className="col-md-6">

        <label className="form-label">
          State
        </label>

        <input
          type="text"
          name="state"
          value={profileData.state}
          onChange={handleChange}
          className="form-control"
        />

      </div>

      {/* CITY */}

      <div className="col-md-6">

        <label className="form-label">
          City
        </label>

        <input
          type="text"
          name="city"
          value={profileData.city}
          onChange={handleChange}
          className="form-control"
        />

      </div>

    </div>

  );

};

export default EditProfileBasic;