import React from "react";

import g1 from "../../assets/images/g1.jpg";
import g2 from "../../assets/images/g2.jpg";
import hero from "../../assets/images/hero.jpg";
import elena from "../../assets/images/elena.jpg";
import julian from "../../assets/images/julian.jpg";

const Gallery = () => {
  const galleryImages = [
    {
      id: 1,
      image: g1,
      caption: "Emi & Sarah · Last Saturday",
    },
    {
      id: 2,
      image: g2,
    },
    {
      id: 3,
      image: hero,
    },
    {
      id: 4,
      image: elena,
    },
    {
      id: 5,
      image: julian,
    },
  ];

  return (
    <section className="section pt-0">
      <div className="container">
        {/* Heading */}
        <h2 className="section-title reveal">
          The Spark Gallery
        </h2>

        <p className="section-sub reveal">
          Witness the chemistry of Spark.
          Real stories, real moments,
          captured live.
        </p>

        {/* Gallery */}
        <div className="gallery reveal">
          {galleryImages.map((item) => (
            <div
              className="g-item"
              key={item.id}
            >
              <img
                src={item.image}
                alt="Gallery"
              />

              {item.caption && (
                <div className="cap">
                  {item.caption}
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </section>
  );
};

export default Gallery;