import React from 'react'

const Journey = () => {
  return (
    <>
    <section className="section">
        <div className="container">
            <h2 className="section-title reveal">Choose Your Journey</h2>

            <p className="section-sub reveal">
            Select the membership that aligns with your pursuit of connection.
            </p>

            <div className="row g-4 justify-content-center">

            <div className="col-md-4 reveal">
                <div className="card-spark price-card">

                <h4>Basic</h4>

                <div className="price">
                    $0
                    <small
                    style={{
                        fontSize: "1rem",
                        color: "var(--muted)"
                    }}
                    >
                    /mo
                    </small>
                </div>

                <ul>
                    <li>Daily matches</li>
                    <li>Standard discovery</li>
                    <li>Priority matching</li>
                </ul>

                <a href="signup.html" className="btn btn-ghost w-100">
                    Select Basic
                </a>

                </div>
            </div>

            <div className="col-md-4 reveal d1">
                <div className="card-spark price-card featured glow">

                <span className="chip active mb-3">
                    Most popular
                </span>

                <h4>Pro</h4>

                <div className="price">
                    $29
                    <small
                    style={{
                        fontSize: "1rem",
                        color: "var(--muted)"
                    }}
                    >
                    /mo
                    </small>
                </div>

                <ul>
                    <li>Unlimited sparks</li>
                    <li>Travel mode</li>
                    <li>See who likes you</li>
                </ul>

                <a href="signup.html" className="btn btn-spark w-100">
                    Join Pro
                </a>

                </div>
            </div>

            <div className="col-md-4 reveal d2">
                <div className="card-spark price-card">

                <h4>Elite</h4>

                <div className="price">
                    $99
                    <small
                    style={{
                        fontSize: "1rem",
                        color: "var(--muted)"
                    }}
                    >
                    /mo
                    </small>
                </div>

                <ul>
                    <li>Priority workspace</li>
                    <li>Personal matchmaker</li>
                    <li>Exclusive VIP access</li>
                </ul>

                <a href="signup.html" className="btn btn-ghost w-100">
                    Join Elite
                </a>

                </div>
            </div>

            </div>
        </div>
    </section>
    </>
  )
}

export default Journey
