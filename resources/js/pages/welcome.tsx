import { Head, Link, usePage } from "@inertiajs/react";
import { type SharedData } from "@/types";
import { router } from "@inertiajs/react";
import { useEffect } from "react";

export default function welcome() {
  const { pharmacies, auth } = usePage<SharedData>().props;
  console.log(auth);
  return (
    <div className="min-h-screen bg-white text-green-700">
      <Head title="UberPharma - Accueil" />

      {/* Header style UberEats */}
      <header className="bg-green-700 text-white p-6 flex justify-between items-center shadow-md">
        <div className="flex items-center gap-2">
          <svg
            width="40"
            height="40"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle cx="20" cy="20" r="20" fill="#22c55e" />
            <text
              x="50%"
              y="55%"
              textAnchor="middle"
              fill="white"
              fontSize="18"
              fontWeight="bold"
              dy=".3em"
            >
              UP
            </text>
          </svg>
          <span className="text-2xl font-bold tracking-tight">UberPharma</span>
        </div>
        <nav className="flex gap-4">
          {auth?.role === "admin" && (
            <Link
              href={route("admin.dashboard")}
              className="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Admin Dashboard
            </Link>
          )}

          {auth ? (
            <div className="flex items-center gap-4">
              <span>Bonjour {auth.name}</span>
              <button
                onClick={() => router.post(route("logout"))} // utilisez route('logout') pour générer l'URL Laravel
                className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
              >
                Déconnexion
              </button>
            </div>
          ) : (
            <>
              <Link href="/login" className="hover:underline">
                Connexion
              </Link>
              <Link href="/register" className="hover:underline">
                Inscription
              </Link>
            </>
          )}
        </nav>
      </header>

      {/* Hero section */}
      <section className="bg-gradient-to-r from-green-600 to-green-400 text-white py-16 px-4 text-center">
        <h1 className="text-5xl md:text-6xl font-extrabold mb-4">
          Trouvez votre pharmacie
        </h1>
        <p className="text-xl md:text-2xl mb-6">
          Commandez vos médicaments en toute simplicité, livraison rapide et
          sécurisée.
        </p>
      </section>

      {/* Pharmacies grid */}
      <main className="max-w-5xl mx-auto py-10 px-4">
        <h2 className="text-3xl font-bold mb-8 text-green-700">
          Pharmacies disponibles
        </h2>
        <Link
          href={route("pharmacies.create")}
          className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          Ajouter une pharmacie
        </Link>
        {pharmacies && pharmacies.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            {pharmacies.map((pharmacy: any) => (
              <Link
                key={pharmacy.id}
                href={route("pharmacies.show", pharmacy.id)}
                className="bg-white border border-green-100 rounded-xl shadow hover:shadow-lg transition-shadow p-6 flex flex-col items-start hover:scale-105 transform"
              >
                <div className="flex items-center gap-3 mb-3">
                  <div className="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-xl">
                    {pharmacy.name.slice(0, 2).toUpperCase()}
                  </div>
                  <span className="text-xl font-semibold">{pharmacy.name}</span>
                </div>
                <div className="text-gray-600 mb-2">
                  <span className="font-medium">Adresse : </span>
                  {pharmacy.address || "Non renseignée"}
                </div>
                <div className="text-gray-600">
                  <span className="font-medium">Téléphone : </span>
                  {pharmacy.phone || "Non renseigné"}
                </div>
              </Link>
            ))}
          </div>
        ) : (
          <div className="text-center text-gray-500">
            Aucune pharmacie disponible pour le moment.
          </div>
        )}
      </main>
    </div>
  );
}
