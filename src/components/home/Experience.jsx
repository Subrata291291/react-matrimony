import React from "react";
import { useScrollReveal } from "../../hooks";

const Experience = () => {
  useScrollReveal();

  const experienceCards = [
    {
      id: 1,
      icon: "bi bi-stars",
      title: "Exclusive Events",
      description:
        "From nightingale-lit gatherings to private dinners, Spark members get access to fluid spaces designed for meaningful mingling.",
    },
    {
      id: 2,
      icon: "bi bi-shield-check",
      title: "Verified Community",
      description:
        "Safety is our cornerstone. Every profile is human-verified to ensure you connect with real people, never bots.",
    },
    {
      id: 3,
      icon: "bi bi-heart-pulse",
      title: "AI Compatibility",
      description:
        "Our proprietary engine learns what you care about, surfacing partners whose values match yours and whose timing is right.",
    },
  ];

  return (
    <section
      className="section"
      id="experience"
    >
      <div className="container">
        {/* Section Heading */}
        <h2 className="section-title reveal">
          The Spark Experience
        </h2>

        <p className="section-sub reveal">
          Three reasons our members keep coming back.
        </p>

        {/* Cards */}
        <div className="row g-4">
          {experienceCards.map((card) => (
            <div
              className="col-md-4 reveal"
              key={card.id}
            >
              <div
                className="card-spark tilt-3d"
                data-tilt
              >
                <div className="tilt-inner">
                  {/* Icon */}
                  <div className="card-icon">
                    <i className={card.icon}></i>
                  </div>

                  {/* Title */}
                  <h4>{card.title}</h4>

                  {/* Description */}
                  <p className="text-muted-2">
                    {card.description}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Experience;