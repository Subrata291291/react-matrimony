import React, { useEffect, useState } from "react";
import toast from "react-hot-toast";
import axios from "axios";

import { Link } from "react-router-dom";

import MainLayout from "../components/layout/MainLayout";

import { useScrollReveal } from "../hooks";

const defaultProfile =
  "https://ui-avatars.com/api/?name=User";

const Discover = () => {

  useScrollReveal();

  /*
  -----------------------------------
  STATES
  -----------------------------------
  */

  const [profiles, setProfiles] =
    useState([]);

  const [
    filteredProfiles,
    setFilteredProfiles,
  ] = useState([]);

  const [currentIndex, setCurrentIndex] =
    useState(0);

  const [maxAge, setMaxAge] =
  useState(32);

const [citySearch, setCitySearch] =
  useState("");

const [interests, setInterests] =
  useState([]);

const [
  selectedInterests,
  setSelectedInterests,
] = useState([]);

const [
  verifiedOnly,
  setVerifiedOnly,
] = useState(false);

const [genderFilter, setGenderFilter] =
  useState("");

  /*
  -----------------------------------
  REFRESH PROFILES
  -----------------------------------
  */

  const handleRefresh = () => {

    const shuffled =
      [...filteredProfiles].sort(
        () => Math.random() - 0.5
      );

    setFilteredProfiles(shuffled);

    setCurrentIndex(0);

  };

  /*
  -----------------------------------
  FETCH MEMBERS
  -----------------------------------
  */

  useEffect(() => {

    axios
      .get(
        "https://projapatibengalimatrimony.in/wp-json/wpm/v1/members"
      )
      .then((res) => {

        /*
        REMOVE INVALID USERS
        */

        const cleaned =
          res.data.filter(
            (item) => {

              const validName =
                item.name &&
                item.name.trim() !==
                  "" &&
                item.name !== "," &&
                item.name.length > 2;

              return (
                validName &&
                item.age &&
                item.profession &&
                item.city
              );

            }
          );

        setProfiles(cleaned);

        setFilteredProfiles(
          cleaned
        );

        axios
          .get(
            "https://projapatibengalimatrimony.in/wp-json/wpm/v1/interests"
          )
          .then((response) => {

            setInterests(
              response.data
            );

          });

      })
      .catch((err) => {

        console.log(err);

      });

  }, []);

  /*
  -----------------------------------
  FILTER AGE
  -----------------------------------
  */

  const handleSearch = () => {

  const filtered =
    profiles.filter((item) => {

      /*
      AGE FILTER
      */

      const ageMatch =
        Number(item.age) <=
        Number(maxAge);

      /*
      CITY FILTER
      */

      const cityMatch =
        citySearch === "" ||
        item.city
          ?.toLowerCase()
          .includes(
            citySearch.toLowerCase()
          );

      /*
      INTEREST FILTER
      */

      let interestMatch = true;

      if (
        selectedInterests.length > 0
      ) {

        const userHobbies =
          item.hobbies
            ? item.hobbies
                .split(",")
                .map((h) =>
                  h.trim()
                    .toLowerCase()
                )
            : [];

        interestMatch =
          selectedInterests.some(
            (interest) =>
              userHobbies.includes(
                interest.toLowerCase()
              )
          );

      }

      /*
      VERIFIED FILTER
      */

      const verifiedMatch =
        !verifiedOnly ||
        item.verified === true ||
        item.is_verified === true;

      /*
      GENDER FILTER
      */

      const userGender =
        item.gender
          ?.toLowerCase()
          ?.trim();

      let genderMatch = true;

      if (genderFilter === "men") {

        genderMatch =
          userGender === "man" ||
          userGender === "men" ||
          userGender === "male";

      }

      if (genderFilter === "women") {

        genderMatch =
          userGender === "woman" ||
          userGender === "women" ||
          userGender === "female";

      }

      return (
        ageMatch &&
        cityMatch &&
        interestMatch &&
        verifiedMatch &&
        genderMatch
      );

    });

  setFilteredProfiles(filtered);

  setCurrentIndex(0);

};

  /*
  -----------------------------------
  CURRENT PROFILE
  -----------------------------------
  */

  const currentProfile =
    filteredProfiles[
      currentIndex
    ];

  /*
  -----------------------------------
  TOP PICKS
  -----------------------------------
  */

  const topPicks =
    filteredProfiles.slice(0, 5);

  /*
  -----------------------------------
  NEXT PROFILE
  -----------------------------------
  */

  const handleNextProfile =
    () => {

      if (
        currentIndex <
        filteredProfiles.length -
          1
      ) {

        setCurrentIndex(
          currentIndex + 1
        );

      } else {

        setCurrentIndex(0);

      }

    };

  /*
  -----------------------------------
  SELECT PROFILE
  -----------------------------------
  */

  const handleSelectProfile =
    (profileId) => {

      const index =
        filteredProfiles.findIndex(
          (item) =>
            item.id ===
            profileId
        );

      if (index !== -1) {

        setCurrentIndex(index);

      }

    };

  /*
  -----------------------------------
  SEND SPARK
  -----------------------------------
  */

  const handleSendSpark = async (userId) => {

  try {

    const loggedInUser = JSON.parse(
      localStorage.getItem("spark_user")
    );

    const formData = new FormData();

    formData.append(
      "action",
      "wpm_send_interest"
    );

    formData.append(
      "sender_id",
      loggedInUser.id
    );

    formData.append(
      "user_id",
      userId
    );

    const response = await axios.post(
      "https://projapatibengalimatrimony.in/wp-admin/admin-ajax.php",
      formData,
      {
        withCredentials: true,
      }
    );

    console.log(response.data);

    toast.success("Spark sent ❤️");

  } catch (error) {

    console.log(error);

    toast.error("Login to send spark");

  }

};

  return (

    <MainLayout>

      <main
        className="container"
        style={{
          paddingTop: "110px",
          paddingBottom: "60px",
        }}
      >

        <div className="row g-4">

          {/* LEFT FILTER */}

          <aside className="col-lg-3">

            <div className="sidebar">

              <h5>

                <i className="bi bi-sliders me-2"></i>

                Filters

              </h5>

              <label className="text-muted-2 small">

                Age Range · 18 – {maxAge}

              </label>

              <input
                type="range"
                className="form-range"
                min="18"
                max="60"
                value={maxAge}
                onChange={(e) =>
                  setMaxAge(
                    e.target.value
                  )
                }
              />

              {/* CITY SEARCH */}

              <label className="text-muted-2 small mt-3">

                Search City

              </label>

              <input
                type="text"
                className="form-control mt-2"
                placeholder="Enter city"
                value={citySearch}
                onChange={(e) =>
                  setCitySearch(
                    e.target.value
                  )
                }
              />

              {/* GENDER FILTER */}

              <label className="text-muted-2 small mt-3">

                Looking For

              </label>

              <select
                className="form-select mt-2"
                value={genderFilter}
                onChange={(e) =>
                  setGenderFilter(
                    e.target.value
                  )
                }
              >

                <option value="">
                  All
                </option>

                <option value="men">
                  Men
                </option>

                <option value="women">
                  Women
                </option>

              </select>

              {/* INTERESTS */}

              <h5 className="mt-4">

                Interests

              </h5>

              <div className="d-flex flex-wrap gap-2">

                {interests.map(
                  (
                    interest,
                    index
                  ) => {

                    const active =
                      selectedInterests.includes(
                        interest
                      );

                    return (

                      <span
                        key={index}
                        onClick={() => {

                          if(active){

                            setSelectedInterests(
                              selectedInterests.filter(
                                (i) =>
                                  i !== interest
                              )
                            );

                          } else {

                            setSelectedInterests([
                              ...selectedInterests,
                              interest,
                            ]);

                          }

                        }}
                        className={`chip ${
                          active
                            ? "active"
                            : ""
                        }`}
                        style={{
                          cursor:
                            "pointer",
                        }}
                      >

                        {interest}

                      </span>

                    );

                  }
                )}

              </div>

              {/* <div className="form-check form-switch mt-4">

                <input
                  className="form-check-input"
                  type="checkbox"
                  id="vo"
                  checked={verifiedOnly}
                  onChange={() =>
                    setVerifiedOnly(
                      !verifiedOnly
                    )
                  }
                />

                <label
                  className="form-check-label"
                  htmlFor="vo"
                >

                  Verified Only

                </label>

              </div> */}

              <button
                className="btn btn-spark w-100 mt-3"
                onClick={
                  handleSearch
                }
              >

                Update Search

              </button>

            </div>

          </aside>

          {/* MIDDLE PROFILE */}

          <section className="col-lg-6">

            {currentProfile ? (

              <div className="profile-card tilt-3d">

                <div className="tilt-inner">

                  <div
                    className="text-center py-2"
                    style={{
                      background:
                        "var(--surface-2)",
                      color:
                        "var(--muted)",
                      fontSize:
                        ".9rem",
                    }}
                  >

                    <i
                      className="bi bi-fire"
                      style={{
                        color:
                          "var(--accent)",
                      }}
                    ></i>

                    {" "}
                    Pick Match

                  </div>

                  {/* PHOTO */}

                  <div className="photo">

                    <img
                      src={
                        currentProfile.photo
                          ? currentProfile.photo
                          : defaultProfile
                      }
                      onError={(e) => {

                        e.target.src =
                          defaultProfile;

                      }}
                      alt={
                        currentProfile.name
                      }
                    />

                    <div className="overlay">

                      <h3>

                        {
                          currentProfile.name
                        }

                        ,
                        {" "}
                        {
                          currentProfile.age
                        }

                      </h3>

                      <p className="mb-0 text-muted-2">

                        {
                          currentProfile.profession
                        }

                        {" · "}

                        {
                          currentProfile.city
                        }

                      </p>

                    </div>

                  </div>

                  {/* ACTIONS */}

                  <div className="actions">

                    {/* NEXT */}

                    <button
                      className="action-btn"
                      onClick={
                        handleNextProfile
                      }
                    >

                      <i className="bi bi-x-lg"></i>

                    </button>

                    {/* SEND SPARK */}

                    <button
                      className="btn-spark"
                      onClick={
                        handleSendSpark
                      }
                    >

                      <i className="bi bi-heart-fill"></i>

                      {" "}
                      Send Spark

                    </button>

                    {/* STAR */}

                    <button className="action-btn">

                      <i className="bi bi-star-fill"></i>

                    </button>

                  </div>

                </div>

              </div>

            ) : (

              <div className="profile-card p-5 text-center">

                <h4>
                  No profiles found
                </h4>

              </div>

            )}

          </section>

          {/* RIGHT TOP PICKS */}

          <aside className="col-lg-3">

            <div className="sidebar">

              <h5>

                <i className="bi bi-stars me-2"></i>

                Top Picks

                <span
                  className="float-end text-muted-2 small"
                  style={{
                    cursor: "pointer",
                  }}
                  onClick={handleRefresh}
                >

                  Refresh

                </span>

              </h5>

              {topPicks.map(
                (
                  pick,
                  index
                ) => (

                  <div
                    key={pick.id}
                    onClick={() =>
                      handleSelectProfile(
                        pick.id
                      )
                    }
                    className={`top-pick-item d-flex align-items-center gap-3 py-2 ${
                      index ===
                      topPicks.length -
                        1
                        ? "no-border"
                        : ""
                    }`}
                    style={{
                      cursor:
                        "pointer",
                    }}
                  >

                    <img
                      className="avatar"
                      src={
                        pick.photo
                          ? pick.photo
                          : defaultProfile
                      }
                      onError={(e) => {

                        e.target.src =
                          defaultProfile;

                      }}
                      alt={
                        pick.name
                      }
                    />

                    <div>

                      <strong>

                        {pick.name},
                        {" "}
                        {pick.age}

                      </strong>

                      <div className="small text-muted-2">

                        {
                          pick.profession
                        }

                        {" · "}

                        {
                          pick.city
                        }

                      </div>

                    </div>

                  </div>

                )
              )}

              {/* UPGRADE */}

              <div
                className="card-spark mt-3 text-center"
                style={{
                  padding:
                    "16px",
                }}
              >

                <i
                  className="bi bi-stars"
                  style={{
                    color:
                      "var(--accent)",
                    fontSize:
                      "1.5rem",
                  }}
                ></i>

                <h6 className="mt-2">

                  Upgrade to Gold

                </h6>

                <p className="small text-muted-2 mb-2">

                  See unlimited
                  top picks and
                  who already
                  liked you.

                </p>

                <Link
                  to="/register"
                  className="btn btn-spark btn-sm w-100"
                >

                  Upgrade Now

                </Link>

              </div>

            </div>

          </aside>

        </div>

      </main>

    </MainLayout>

  );

};

export default Discover;