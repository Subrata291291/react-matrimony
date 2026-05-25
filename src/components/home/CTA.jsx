import React from 'react'
import { Link } from "react-router-dom"

const CTA = () => {
  return (
    <>
    <section className="section text-center reveal">
        <div className="container">
            <h2 className="section-title">Your story begins tonight</h2>
            <p className="section-sub">Don't let another twilight pass by. Join the most exclusive community of romantics worldwide and find your lavender spark.</p>
            <Link to="/register" className="btn btn-spark glow">Get Started</Link>
        </div>
    </section>
    </>
  )
}

export default CTA
