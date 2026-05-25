import React, { useEffect,useState, } from "react";
import MainLayout from "../components/layout/MainLayout";
import EditProfileBasic from "../components/profile/EditProfileBasic";

import EditProfileAbout from "../components/profile/EditProfileAbout";
import EditProfileMedia from "../components/profile/EditProfileMedia";

import EditProfileGallery from "../components/profile/EditProfileGallery";
import { getProfile, updateProfile, } from "../services/profileService";

import toast from "react-hot-toast";

const EditProfile = () => {
  /*
  -----------------------------------
  USER
  -----------------------------------
  */

  const user = JSON.parse(
    localStorage.getItem("spark_user")
  );

  /*
  -----------------------------------
  STATES
  -----------------------------------
  */

  const [loading, setLoading] =
    useState(true);

  const [saving, setSaving] =
    useState(false);

  const [profileData, setProfileData] =
  useState({

    full_name: "",

    gender: "",

    looking_for: "",

    dob: "",

    religion: "",

    education: "",

    profession: "",

    country: "",

    state: "",

    city: "",

    about: "",

    hobbies: "",

    partner_expectations: "",

  });

  const [profilePreview, setProfilePreview] =
  useState("");

  const [coverPreview, setCoverPreview] =
    useState("");

  const [galleryPreview, setGalleryPreview] =
    useState([]);

  const handleProfilePhoto =
  (e) => {

    const file =
      e.target.files[0];

    if(file){

      setProfileData({
        ...profileData,
        profile_photo: file,
      });

      setProfilePreview(
        URL.createObjectURL(file)
      );

    }

};
const handleCoverPhoto =
  (e) => {

    const file =
      e.target.files[0];

    if(file){

      setProfileData({
        ...profileData,
        cover_photo: file,
      });

      setCoverPreview(
        URL.createObjectURL(file)
      );

    }

};
const handleGallery =
  (e) => {

    const files =
      Array.from(
        e.target.files
      );

    setProfileData({
      ...profileData,
      gallery: files,
    });

    const previews =
      files.map((file)=>
        URL.createObjectURL(file)
      );

    setGalleryPreview(
      previews
    );

    };
      /*
      -----------------------------------
      FETCH PROFILE
      -----------------------------------
      */

      useEffect(() => {
        fetchProfile();
      }, []);

      /*
      -----------------------------------
      GET PROFILE
      -----------------------------------
      */

      const fetchProfile = async () => {
        try {
          const data = await getProfile(
            user.id
          );

          setProfileData({

            full_name:
              data.name ||
              user.name ||
              "",

            gender:
              data.gender || "",

            looking_for:
              data.looking_for || "",

            dob:
              data.dob || "",

            religion:
              data.religion || "",

            education:
              data.education || "",

            profession:
              data.profession || "",

            country:
              data.country || "",

            state:
              data.state || "",

            city:
              data.city || "",

            about:
              data.about || "",

            hobbies:
              data.hobbies || "",

            partner_expectations:
              data.partner_expectations || "",

          });

        /*
        -----------------------------------
        SET IMAGE PREVIEWS
        -----------------------------------
        */

        setProfilePreview(
          data.profile_photo || ""
        );

        setCoverPreview(
          data.cover_photo || ""
        );

        setGalleryPreview(
          data.gallery || []
        );
        } catch (error) {
          toast.error(
            "Failed to load profile"
          );
        } finally {
          setLoading(false);
        }
      };

  /*
  -----------------------------------
  HANDLE CHANGE
  -----------------------------------
  */

  const handleChange = (e) => {
    setProfileData({
      ...profileData,

      [e.target.name]:
        e.target.value,
    });
  };

  /*
  -----------------------------------
  HANDLE SUBMIT
  -----------------------------------
  */

  const handleSubmit = async (e) => {
    e.preventDefault();

    setSaving(true);

    try {
      const formData =
        new FormData();

      formData.append(
        "user_id",
        user.id
      );

      Object.keys(profileData).forEach(
        (key) => {

          if(key === "gallery"){

            profileData.gallery.forEach(
              (file) => {

                formData.append(
                  "gallery",
                  file
                );

            });

          }

          else{

            if(profileData[key] !== ""){

              formData.append(
                key,
                profileData[key]
              );

            }

          }

      });

      await updateProfile(formData);

      toast.success(
        "Profile updated successfully"
      );
    } catch (error) {
      toast.error(
        "Profile update failed"
      );
    } finally {
      setSaving(false);
    }
  };

  /*
  -----------------------------------
  LOADING
  -----------------------------------
  */

  if (loading) {
    return (
      <div className="text-center text-white py-5">
        Loading...
      </div>
    );
  }

  return (
    <MainLayout>
      <section className="section">
        <div className="container">
          {/* COVER */}

        <div
          className="cover"
          style={{
            backgroundImage: `url(${
              coverPreview ||
              "https://ui-avatars.com/api/?name=Cover"
            })`,

            backgroundSize: "cover",

            backgroundPosition: "center",
          }}
        ></div>

      {/* PROFILE HEADER */}

      <div className="profile-head">

        <img
          className="pa float"
          src={
            profilePreview ||
            "https://ui-avatars.com/api/?name=User"
          }
          alt=""
        />

        <div className="flex-grow-1">

          <h2 className="m-0">

            {profileData.full_name}

            {profileData.dob &&
              `, ${
                new Date().getFullYear() -
                new Date(profileData.dob).getFullYear()
              }`
            }

          </h2>

          <div className="text-muted-2">

            {profileData.profession}

            {" · "}

            {profileData.city}

          </div>

        </div>

      </div>
          <div
            className="card-spark p-4 mx-auto mt-5">
            {/* Heading */}

            <div className="mb-4">
              <h2 className="section-title">
                Edit Profile
              </h2>

              <p className="section-sub">
                Keep your profile updated
                and attractive.
              </p>
            </div>

            {/* FORM */}

            <form
              onSubmit={handleSubmit}
            >
              <EditProfileBasic
                profileData={profileData}
                handleChange={handleChange}
              />

              <EditProfileAbout
                profileData={profileData}
                handleChange={handleChange}
              />
              <EditProfileMedia

                profileData={profileData}

                profilePreview={profilePreview}
                coverPreview={coverPreview}

                handleProfilePhoto={
                  handleProfilePhoto
                }

                handleCoverPhoto={
                  handleCoverPhoto
                }

              />

              <EditProfileGallery

                galleryPreview={
                  galleryPreview
                }

                handleGallery={
                  handleGallery
                }

              />

              <div className="mt-4">

                <button
                  type="submit"
                  className="btn btn-spark glow px-5"
                  disabled={saving}
                >

                  {saving
                    ? "Saving..."
                    : "Save Profile"}

                </button>

              </div>
            </form>
          </div>
        </div>
      </section>
    </MainLayout>
  );
};

export default EditProfile;
