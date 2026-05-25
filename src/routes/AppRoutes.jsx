import { BrowserRouter, Routes, Route } from "react-router-dom";

import Home from "../pages/Home";
import Discover from "../pages/Discover";
import Messages from "../pages/Messages";
import Profile from "../pages/Profile";
import Login from "../pages/Login";
import Register from "../pages/Register";
import EditProfile from "../pages/EditProfile";

import ProtectedRoute from "./ProtectedRoute";

function AppRoutes() {

  return (

    <BrowserRouter basename="/app">

      <Routes>

        <Route
          path="/"
            element={
            <ProtectedRoute>
              <Home />
            </ProtectedRoute>
          }
        />

        <Route
          path="/discover"
           element={
            <ProtectedRoute>
              <Discover />
            </ProtectedRoute>
          }
        />

        <Route
          path="/messages"
          element={
            <ProtectedRoute>
              <Messages />
            </ProtectedRoute>
          }
        />

        <Route
          path="/profile/:id"
          element={
            <ProtectedRoute>
              <Profile />
            </ProtectedRoute>
          }
        />

        <Route
          path="/login"
          element={<Login />}
        />

        <Route
          path="/register"
          element={<Register />}
        />

        <Route
          path="/edit-profile/:id"
          element={
            <ProtectedRoute>
              <EditProfile />
            </ProtectedRoute>
          }
        />

      </Routes>

    </BrowserRouter>

  );

}

export default AppRoutes;