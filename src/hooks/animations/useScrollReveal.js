import { useEffect } from "react";

const useScrollReveal = () => {

  useEffect(() => {

    const elements =
      document.querySelectorAll(
        ".reveal"
      );

    const observer =
      new IntersectionObserver(
        (entries) => {

          entries.forEach(
            (entry) => {

              if (
                entry.isIntersecting
              ) {

                entry.target.classList.add(
                  "in"
                );

              }

            }
          );

        },
        {
          threshold: 0.1,
        }
      );

    elements.forEach((el) => {

      observer.observe(el);

    });

    return () => {

      elements.forEach((el) => {

        observer.unobserve(el);

      });

    };

  });

};

export default useScrollReveal;