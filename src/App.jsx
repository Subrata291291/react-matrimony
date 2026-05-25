import AppRoutes from "./routes/AppRoutes";
import { useScrollReveal } from "./hooks";

function App() {

  useScrollReveal();

  return <AppRoutes />;
}

export default App;