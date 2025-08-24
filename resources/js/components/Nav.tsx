import { Link, router, usePage } from "@inertiajs/react";
import { useState } from "react";
import { type SharedData } from "@/types";

export default function Nav() {
  const { auth } = usePage<SharedData>().props;
  const [menuOpen, setMenuOpen] = useState(false);

  const toggleMenu = () => setMenuOpen(!menuOpen);

  return (
    <header className="bg-green-700 text-white shadow-md">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16 items-center">
          {/* Logo */}
          <Link href={route("home")} className="flex items-center gap-2">
            <div className="w-10 h-10 bg-green-100 text-green-700 flex items-center justify-center rounded-full font-bold text-lg">
              UP
            </div>
            <span className="font-bold text-xl tracking-tight">UberPharma</span>
          </Link>

          {/* Desktop Menu */}
          <nav className="hidden md:flex gap-4 items-center">
            <Link href={route("home")} className="hover:underline">
              Accueil
            </Link>

            {auth?.role === "client" && (
              <>
                <Link href={route("orders.index")} className="hover:underline">
                  Mes commandes
                </Link>
                <Link href={route("pharmacies.show", 1)} className="hover:underline">
                  Pharmacies
                </Link>
              </>
            )}

            {auth?.role === "pharmacy" && (
              <>
                <Link href={route("pharmacy.dashboard")} className="hover:underline">
                  Mon Dashboard
                </Link>
                <Link href={route("pharmacy.products.index")} className="hover:underline">
                  Mes produits
                </Link>
                <Link href={route("orders.index")} className="hover:underline">
                  Commandes
                </Link>
              </>
            )}

            {auth?.role === "admin" && (
              <>
                <Link href={route("admin.dashboard")} className="hover:underline">
                  Admin Dashboard
                </Link>
                <Link href={route("admin.users")} className="hover:underline">
                  Utilisateurs
                </Link>
                <Link href={route("admin.pharmacies")} className="hover:underline">
                  Pharmacies
                </Link>
                <Link href={route("admin.products")} className="hover:underline">
                  Produits
                </Link>
              </>
            )}

            {auth ? (
              <div className="ml-4 flex items-center gap-2">
                <span className="font-medium">Bonjour {auth.name}</span>
                <button
                  onClick={() => router.post(route("logout"))}
                  className="px-3 py-1 bg-red-600 rounded hover:bg-red-700 transition"
                >
                  Déconnexion
                </button>
              </div>
            ) : (
              <div className="ml-4 flex items-center gap-2">
                <Link
                  href={route("login")}
                  className="px-3 py-1 bg-blue-600 rounded hover:bg-blue-700 transition"
                >
                  Connexion
                </Link>
                <Link
                  href={route("register")}
                  className="px-3 py-1 bg-green-600 rounded hover:bg-green-700 transition"
                >
                  Inscription
                </Link>
              </div>
            )}
          </nav>

          {/* Mobile Menu Button */}
          <div className="md:hidden flex items-center">
            <button onClick={toggleMenu} className="focus:outline-none">
              <svg
                className="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                {menuOpen ? (
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M6 18L18 6M6 6l12 12"
                  />
                ) : (
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth={2}
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                )}
              </svg>
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {menuOpen && (
        <nav className="md:hidden bg-green-700 px-4 pb-4 flex flex-col gap-2">
          <Link href={route("home")} className="hover:underline">
            Accueil
          </Link>

          {auth?.role === "client" && (
            <>
              <Link href={route("orders.index")} className="hover:underline">
                Mes commandes
              </Link>
              <Link href={route("pharmacies.show", 1)} className="hover:underline">
                Pharmacies
              </Link>
            </>
          )}

          {auth?.role === "pharmacy" && (
            <>
              <Link href={route("pharmacy.dashboard")} className="hover:underline">
                Mon Dashboard
              </Link>
              <Link href={route("pharmacy.products.index")} className="hover:underline">
                Mes produits
              </Link>
              <Link href={route("orders.index")} className="hover:underline">
                Commandes
              </Link>
            </>
          )}

          {auth?.role === "admin" && (
            <>
              <Link href={route("admin.dashboard")} className="hover:underline">
                Admin Dashboard
              </Link>
              <Link href={route("admin.users")} className="hover:underline">
                Utilisateurs
              </Link>
              <Link href={route("admin.pharmacies")} className="hover:underline">
                Pharmacies
              </Link>
              <Link href={route("admin.products")} className="hover:underline">
                Produits
              </Link>
            </>
          )}

          {auth ? (
            <div className="ml-4 flex items-center gap-2">
              <span className="font-medium">Bonjour {auth.name}</span>
              <button
                onClick={() => router.post(route("logout"))}
                className="px-3 py-1 bg-red-600 rounded hover:bg-red-700 transition"
              >
                Déconnexion
              </button>
            </div>
          ) : (
            <div className="ml-4 flex items-center gap-2">
              <Link
                href={route("login")}
                className="px-3 py-1 bg-blue-600 rounded hover:bg-blue-700 transition"
              >
                Connexion
              </Link>
              <Link
                href={route("register")}
                className="px-3 py-1 bg-green-600 rounded hover:bg-green-700 transition"
              >
                Inscription
              </Link>
            </div>
          )}
        </nav>
      )}
    </header>
  );
}
