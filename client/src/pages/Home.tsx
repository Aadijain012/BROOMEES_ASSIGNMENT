/**
 * Architectural Nightshift reminder: this page is the authoritative dashboard shell,
 * favoring an asymmetric control-room layout over a generic centered landing page.
 */
import ControlCenter from "@/components/ControlCenter";

export default function Home() {
  return <ControlCenter />;
}
