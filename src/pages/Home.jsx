import MainLayout from "../components/layout/MainLayout";

import Hero from "../components/home/Hero";
import Experience from "../components/home/Experience";
import Gallery from "../components/home/Gallery";
import Journey from "../components/home/Journey";
import Pricing from "../components/home/Pricing";
import CTA from "../components/home/CTA";

function Home() {
  return (
    <MainLayout>
      <Hero />
      <Experience />
      <Gallery />
      <Journey />
      <Pricing />
      <CTA />
    </MainLayout>
  );
}

export default Home;