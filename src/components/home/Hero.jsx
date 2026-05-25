import React from 'react'
import { Link } from "react-router-dom"
import heroImg from "../../assets/images/hero.jpg";

const Hero = () => {
  return (
    <>
      <div
        className="hero"
        style={{
          backgroundImage: `url(${heroImg})`
        }}
      >
        <div>
            <h1 className="fade-up">Find your <em>spark</em><br/>in a garden of lavender</h1>
            <p className="fade-up d1">Step away from the noise and into a curated space of deep connections, where intimacy is always exactly that — exactly that.</p>
            <div className="fade-up d2">
            <Link to="/register" className="btn btn-spark me-2">Join Now</Link>
            <Link to="/experience" className="btn btn-ghost">Learn More</Link>
            </div>
        </div>
    </div>
    </>
  )
}

export default Hero
