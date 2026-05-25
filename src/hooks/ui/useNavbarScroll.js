import { useEffect, useState } from "react";

function useNavbarScroll() {

  const [sticky, setSticky] = useState(false);

  useEffect(() => {

    const handleScroll = () => {
      setSticky(window.scrollY > 50);
    };

    window.addEventListener("scroll", handleScroll);

    return () => {
      window.removeEventListener("scroll", handleScroll);
    };

  }, []);

  return sticky;
}

export default useNavbarScroll;