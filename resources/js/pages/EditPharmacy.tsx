import { useState } from "react";
import { router, usePage } from "@inertiajs/react";
import { Head } from "@inertiajs/react";
import Nav from '../components/Nav';
import { type SharedData } from "@/types";


export default function EditPharmacy({ pharmacy }: any) {
  const { auth } = usePage<SharedData>().props;
  const [form, setForm] = useState({
    name: pharmacy.name || "",
    address: pharmacy.address || "",
    phone: pharmacy.phone || "",
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.put(`/pharmacies/${pharmacy.id}`, form);
  };

  return (
    <div className="min-h-screen bg-white text-green-700">
      <Nav />
      <Head title={`Modifier ${pharmacy.name}`} />
      <main className="max-w-3xl mx-auto p-6">
        <h1 className="text-2xl font-bold mb-4">Modifier la pharmacie</h1>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block font-medium">Nom</label>
            <input
              type="text"
              name="name"
              value={form.name}
              onChange={handleChange}
              className="w-full rounded border p-2"
              required
            />
          </div>

          <div>
            <label className="block font-medium">Adresse</label>
            <input
              type="text"
              name="address"
              value={form.address}
              onChange={handleChange}
              className="w-full rounded border p-2"
            />
          </div>

          <div>
            <label className="block font-medium">Téléphone</label>
            <input
              type="text"
              name="phone"
              value={form.phone}
              onChange={handleChange}
              className="w-full rounded border p-2"
            />
          </div>

          <div className="text-right">
            <button
              type="submit"
              className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
            >
              Enregistrer
            </button>
          </div>
        </form>
      </main>
    </div>
  );
}
