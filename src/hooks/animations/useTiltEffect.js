import { useEffect } from "react";

const useTiltEffect = () => {
  useEffect(() => {
    const cards = document.querySelectorAll("[data-tilt]");

    cards.forEach((card) => {
      const inner = card.querySelector(".tilt-inner") || card;

      const handleMouseMove = (e) => {
        const r = card.getBoundingClientRect();

        const x = (e.clientX - r.left) / r.width - 0.5;
        const y = (e.clientY - r.top) / r.height - 0.5;

        inner.style.transform = `
          perspective(1000px)
          rotateY(${x * 12}deg)
          rotateX(${-y * 12}deg)
          translateZ(10px)
        `;
      };

      const handleLeave = () => {
        inner.style.transform = "";
      };

      card.addEventListener("mousemove", handleMouseMove);
      card.addEventListener("mouseleave", handleLeave);

      return () => {
        card.removeEventListener("mousemove", handleMouseMove);
        card.removeEventListener("mouseleave", handleLeave);
      };
    });
  }, []);
};

export default useTiltEffect;