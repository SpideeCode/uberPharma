import { useState } from "react";
import { Link } from "@inertiajs/react";
import Nav from "@/components/Nav";

export default function Dashboard({ pharmacies }: any) {
  return (
    <div >
      <Nav />
      <h1 className="text-2xl font-bold mb-4">Dashboard Pharmacie</h1>

      {pharmacies.length === 0 ? (
        <p>Vous n’avez pas encore de pharmacies.</p>
      ) : (
        <ul className="space-y-2">
          {pharmacies.map((pharmacy: any) => (
            <li
              key={pharmacy.id}
              className="p-4 border rounded flex justify-between items-center"
            >
              <div>
                <h2 className="font-semibold">{pharmacy.name}</h2>
                <p className="text-sm text-gray-600">{pharmacy.address}</p>
                <p className="text-sm text-gray-600">{pharmacy.phone}</p>
              </div>
              <div className="space-x-2">
                <Link
                  href={`/pharmacies/${pharmacy.id}/edit`}
                  className="px-3 py-1 bg-blue-500 text-white rounded"
                >
                  Modifier
                </Link>
                <Link
                  href={`/products?pharmacy_id=${pharmacy.id}`}
                  className="px-3 py-1 bg-green-500 text-white rounded"
                >
                  Produits
                </Link>
              </div>
            </li>
          ))}
        </ul>
      )}

      <div className="mt-6">
        
      </div>
    </div>
  );
}
