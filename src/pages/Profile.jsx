import React, {
  useEffect,
  useState,
} from "react";

import axios from "axios";

import { useParams, Link, useNavigate } from "react-router-dom";

import MainLayout from "../components/layout/MainLayout";

const defaultProfile =
  "https://ui-avatars.com/api/?name=User";

const Profile = () => {
  const navigate = useNavigate();
  const { id } = useParams();

  const loggedInUser = JSON.parse(
    localStorage.getItem("spark_user")
  );

  const isOwnProfile =
    loggedInUser &&
  Number(loggedInUser.id) === Number(id);

  const [profile, setProfile] =
    useState(null);

  const [activeTab, setActiveTab] =
    useState("new");

  const [interestData, setInterestData] =
    useState({
      new_requests: [],
      accepted: [],
      declined: [],
    });

    const [connections, setConnections] = useState([]);

const fetchConnections = () => {

  axios
    .get(
      `https://projapatibengalimatrimony.in/wp-json/wpm/v1/connections/${id}`
    )
    .then((res) => {

      setConnections(res.data);

    })
    .catch((err) => {

      console.log(err);

    });

};

useEffect(() => {

  if (id) {

    fetchConnections();

  }

}, [id]);

  /*
  ===================================
  FETCH PROFILE
  ===================================
  */

  useEffect(() => {

    axios
      .get(
        "https://projapatibengalimatrimony.in/wp-json/wpm/v1/members"
      )
      .then((res) => {

        const found =
          res.data.find(
            (item) =>
              item.id ===
              Number(id)
          );

        setProfile(found);

      })
      .catch((err) => {

        console.log(err);

      });

  }, [id]);

  /*
  ===================================
  FETCH REQUESTS DATA
  ===================================
  */

  const fetchInterestData = () => {

    axios
      .get(
        `https://projapatibengalimatrimony.in/wp-json/wpm/v1/interest-data/${id}`
      )
      .then((res) => {

        setInterestData(res.data);

      })
      .catch((err) => {

        console.log(err);

      });

  };

  useEffect(() => {

    fetchInterestData();

  }, [id]);

  /*
  ===================================
  ACCEPT REQUEST
  ===================================
  */

  const handleAccept = async (userId) => {

  try {

    const formData = new FormData();

    formData.append(
      "action",
      "wpm_accept_interest"
    );

    formData.append(
      "user_id",
      userId
    );

    formData.append(
      "current_user_id",
      id
    );

    await axios.post(
      "https://projapatibengalimatrimony.in/wp-admin/admin-ajax.php",
      formData,
      {
        withCredentials: true,
      }
    );

    /*
    REFRESH REQUEST DATA
    */

    fetchInterestData();
    fetchConnections();

  } catch (error) {

    console.log(error);

    alert("Accept failed");

  }

};

  /*
  ===================================
  DECLINE REQUEST
  ===================================
  */

  const handleDecline = async (userId) => {

  try {

    /*
    -----------------------------------
    DECLINE INTEREST
    -----------------------------------
    */

    const formData = new FormData();

    formData.append(
      "action",
      "wpm_decline_interest"
    );

    formData.append(
      "user_id",
      userId
    );

    formData.append(
      "current_user_id",
      id
    );

    await axios.post(
      "https://projapatibengalimatrimony.in/wp-admin/admin-ajax.php",
      formData,
      {
        withCredentials: true,
      }
    );

    /*
    -----------------------------------
    REMOVE CONNECTION
    -----------------------------------
    */

    await axios.post(
      "https://projapatibengalimatrimony.in/wp-json/wpm/v1/remove-connection",
      {
        user_one: Number(id),
        user_two: Number(userId),
      }
    );

    /*
    -----------------------------------
    REFRESH UI
    -----------------------------------
    */

    fetchInterestData();

    fetchConnections();

  } catch (error) {

    console.log(error);

    alert("Decline failed");

  }

};

  /*
  ===================================
  LOADING
  ===================================
  */

  if (!profile) {

    return (

      <MainLayout>

        <div
          className="container text-center"
          style={{
            paddingTop:
              "150px",
          }}
        >

          <h3>
            Loading Profile...
          </h3>

        </div>

      </MainLayout>

    );

  }

  /*
  ===================================
  HOBBIES
  ===================================
  */

  const hobbies =
    profile.hobbies
      ? profile.hobbies.split(",")
      : [
          "Travel",
          "Music",
          "Photography",
        ];

  /*
  ===================================
  VITALS
  ===================================
  */

  const vitals = [

    {
      label: "Location",
      value: `${profile.city || ""}, ${profile.state || ""}`,
    },

    {
      label: "Profession",
      value:
        profile.profession ||
        "Not Added",
    },

    {
      label: "Education",
      value:
        profile.education ||
        "Not Added",
    },

    {
      label: "Religion",
      value:
        profile.religion ||
        "Not Added",
    },

    {
      label: "Looking For",
      value:
        profile.looking_for ||
        "Not Added",
    },

  ];

  return (

    <MainLayout>

      <main
        className="container"
        style={{
          paddingTop:
            "110px",

          paddingBottom:
            "60px",
        }}
      >

        {/* COVER */}

        <div
          className="cover"
          style={{
            backgroundImage: `url(${
              profile.cover_photo ||
              profile.photo ||
              defaultProfile
            })`,

            backgroundSize:
              "cover",

            backgroundPosition:
              "center",
          }}
        ></div>

        {/* PROFILE HEADER */}

        <div className="profile-head">

          <img
            className="pa float"
            src={
              profile.photo ||
              defaultProfile
            }
            alt={
              profile.name
            }
          />

          <div className="flex-grow-1">

            <h2 className="m-0">

              {
                profile.name
              }

              {profile.age &&
                `, ${profile.age}`}

            </h2>

            <div className="text-muted-2">

              {
                profile.profession
              }

              {" · "}

              {
                profile.city
              }

            </div>

          </div>
          {isOwnProfile && (
          <div className="d-flex gap-2">

            <Link
              to={`/edit-profile/${profile.id}`}
              className="btn btn-spark"
            >

              Edit Profile

            </Link>

            <button
              className="btn btn-ghost"
              onClick={() => {

                localStorage.removeItem(
                  "spark_user"
                );

                navigate("/login");

              }}
            >

              Logout

            </button>

          </div>
          )}
        </div>

        {/* MAIN */}

        <div className="row g-4 mt-3">

          {/* LEFT */}

          <div className="col-lg-7">

            {/* GALLERY */}

            <div className="card-spark">

              <h4>
                Portfolio Gallery
              </h4>

              <div className="row g-3 mt-1">

                {profile.gallery &&
                profile.gallery.length >
                  0 ? (

                  profile.gallery.map(
                    (
                      img,
                      index
                    ) => (

                      <div
                        className="col-4"
                        key={index}
                      >

                        <img
                          src={img}
                          alt=""
                          style={{
                            width:
                              "100%",

                            aspectRatio:
                              "4/5",

                            objectFit:
                              "cover",

                            borderRadius:
                              "14px",
                          }}
                        />

                      </div>

                    )
                  )

                ) : (

                  <div className="col-4">

                    <img
                      src={
                        profile.photo
                      }
                      alt=""
                      style={{
                        width:
                          "100%",

                        aspectRatio:
                          "4/5",

                        objectFit:
                          "cover",

                        borderRadius:
                          "14px",
                      }}
                    />

                  </div>

                )}

              </div>

            </div>

            {/* HOBBIES */}

            <div className="card-spark mt-4">

              <h4>
                Hobbies
              </h4>

              <div className="mt-3">

                {hobbies.map(
                  (
                    hobby,
                    index
                  ) => (

                    <span
                      key={index}
                      className={`chip ${
                        index < 2
                          ? "active"
                          : ""
                      }`}
                    >

                      {hobby}

                    </span>

                  )
                )}

              </div>

            </div>
            
            {/* Connections */}
            {isOwnProfile && (
            <div className="card-spark mt-4">

              <div className="d-flex justify-content-between align-items-center mb-4">

                <h4>Connections</h4>

                <span className="text-muted-2">
                  {connections.length} Friends
                </span>

              </div>

              <div className="connections-list">

                {connections.length > 0 ? (

                  connections.map((item) => (

                    <div
                      key={item.id}
                      className="d-flex justify-content-between align-items-center py-3 border-bottom"
                    >

                      <Link
                        to={`/profile/${item.id}`}
                        className="d-flex align-items-center gap-3 text-decoration-none flex-grow-1"
                      >

                        <img
                          src={
                            item.profile_image ||
                            item.photo ||
                            defaultProfile
                          }
                          alt={item.name}
                          style={{
                            width: "65px",
                            height: "65px",
                            borderRadius: "50%",
                            objectFit: "cover",
                            border: "2px solid #ffffff22",
                          }}
                        />

                        <div>

                          <h5
                            style={{
                              marginBottom: "4px",
                            }}
                          >
                            {item.name}
                            {item.age &&
                              `, ${item.age}`}
                          </h5>

                          <div className="text-muted-2 small">

                            {item.city || "Unknown City"}

                            {item.profession &&
                              ` · ${item.profession}`}

                          </div>

                          <div
                            className="small"
                            style={{
                              color: "#10b981",
                              marginTop: "4px",
                            }}
                          >

                            {item.is_online
                              ? "Online Now"
                              : item.last_seen
                              ? `Last seen ${item.last_seen}`
                              : "Recently Active"}

                          </div>

                        </div>

                      </Link>

                      <div className="d-flex gap-2">

                        <button
                          className="btn btn-spark btn-sm"
                          onClick={() =>
                            navigate(
                              `/messages?user=${item.id}`,
                              {
                                state: {
                                  chatUser: item,
                                },
                              }
                            )
                          }
                        >
                          CHAT NOW
                        </button>

                        <button className="btn btn-ghost btn-sm">
                          BLOCK
                        </button>

                      </div>

                    </div>

                  ))

                ) : (

                  <p className="text-muted-2">
                    No connections yet.
                  </p>

                )}

              </div>

            </div>
            )}
          </div>

          {/* RIGHT */}

          <div className="col-lg-5">
                
            {/* SPARK REQUESTS */}
            {isOwnProfile && (
            <div className="card-spark">

              <h4>
                Spark Requests
              </h4>

              {/* TABS */}

              <div className="d-flex gap-2 mt-3 mb-4">

                <button
                  className={`chip ${
                    activeTab ===
                    "new"
                      ? "active"
                      : ""
                  }`}
                  onClick={() =>
                    setActiveTab(
                      "new"
                    )
                  }
                >
                  New
                </button>

                <button
                  className={`chip ${
                    activeTab ===
                    "accepted"
                      ? "active"
                      : ""
                  }`}
                  onClick={() =>
                    setActiveTab(
                      "accepted"
                    )
                  }
                >
                  Accepted
                </button>

                <button
                  className={`chip ${
                    activeTab ===
                    "declined"
                      ? "active"
                      : ""
                  }`}
                  onClick={() =>
                    setActiveTab(
                      "declined"
                    )
                  }
                >
                  Declined
                </button>

              </div>

              {/* NEW */}

              {activeTab ===
                "new" && (

                <>
                  {interestData
                    .new_requests
                    .length > 0 ? (

                    interestData.new_requests.map(
                      (
                        user
                      ) => (

                        <div
                          key={
                            user.id
                          }
                          className="d-flex align-items-center justify-content-between py-3 border-bottom"
                        >

                          <div className="d-flex align-items-center gap-3">

                            <img
                              src={
                                user.photo &&
                                user.photo !== ""
                                  ? user.photo
                                  : defaultProfile
                              }
                              className="avatar"
                              alt=""
                            />

                            <div>

                              <strong>
                                {
                                  user.name
                                }
                              </strong>

                              <div className="small text-muted-2">

                                Sent you a Spark ❤️

                              </div>

                            </div>

                          </div>

                          {isOwnProfile && (
                          <div className="d-flex gap-2">

                            <button
                              className="btn btn-spark btn-sm"
                              onClick={() =>
                                handleAccept(
                                  user.id
                                )
                              }
                            >
                              Accept
                            </button>

                            <button
                              className="btn btn-ghost btn-sm"
                              onClick={() =>
                                handleDecline(
                                  user.id
                                )
                              }
                            >
                              Decline
                            </button>

                          </div>
                          )}

                        </div>

                      )
                    )

                  ) : (

                    <p className="text-muted-2">

                      No requests yet.

                    </p>

                  )}
                </>

              )}

              {/* ACCEPTED */}

              {activeTab ===
                "accepted" && (

                <>
                  {interestData
                    .accepted
                    .length > 0 ? (

                    interestData.accepted.map(
                      (
                        user
                      ) => (

                        <div
                          key={
                            user.id
                          }
                          className="d-flex align-items-center justify-content-between py-3 border-bottom"
                        >

                          <div className="d-flex align-items-center gap-3">

                            <img
                              src={
                                user.photo &&
                                user.photo !== ""
                                  ? user.photo
                                  : defaultProfile
                              }
                              className="avatar"
                              alt=""
                            />

                            <div>

                              <strong>
                                {
                                  user.name
                                }
                              </strong>

                              <div className="small text-success">

                                Interest Accepted ❤️

                              </div>

                            </div>

                          </div>

                          <button
                            className="btn btn-ghost btn-sm"
                            onClick={() =>
                              handleDecline(
                                user.id
                              )
                            }
                          >
                            Decline
                          </button>

                        </div>

                      )
                    )

                  ) : (

                    <p className="text-muted-2">

                      No accepted requests.

                    </p>

                  )}
                </>

              )}

              {/* DECLINED */}

              {activeTab ===
                "declined" && (

                <>
                  {interestData
                    .declined
                    .length > 0 ? (

                    interestData.declined.map(
                      (
                        user
                      ) => (

                        <div
                          key={
                            user.id
                          }
                          className="d-flex align-items-center justify-content-between py-3 border-bottom"
                        >

                          <div className="d-flex align-items-center gap-3">

                            <img
                              src={
                                user.photo &&
                                user.photo !== ""
                                  ? user.photo
                                  : defaultProfile
                              }
                              className="avatar"
                              alt=""
                            />

                            <div>

                              <strong>
                                {
                                  user.name
                                }
                              </strong>

                              <div className="small text-danger">

                                Interest Declined

                              </div>

                            </div>

                          </div>

                          <button
                            className="btn btn-spark btn-sm"
                            onClick={() =>
                              handleAccept(
                                user.id
                              )
                            }
                          >
                            Accept
                          </button>

                        </div>

                      )
                    )

                  ) : (

                    <p className="text-muted-2">

                      No declined requests.

                    </p>

                  )}
                </>

              )}

            </div>
            )}
            {/* ABOUT */}

            <div className="card-spark mt-4">

              <h4>
                ✦ Narrative
              </h4>

              <p className="text-muted-2">

                {profile.about ||
                  "No bio added yet."}

              </p>

            </div>

            {/* VITALS */}

            <div className="card-spark mt-4">

              <h4>
                Vitals
              </h4>

              {vitals.map(
                (
                  item,
                  index
                ) => (

                  <div
                    className="vital"
                    key={index}
                  >

                    <span>
                      {
                        item.label
                      }
                    </span>

                    <span>
                      {
                        item.value
                      }
                    </span>

                  </div>

                )
              )}

            </div>

          </div>

        </div>

      </main>

    </MainLayout>

  );

};

export default Profile;
