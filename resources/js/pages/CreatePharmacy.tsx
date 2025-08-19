import { router } from '@inertiajs/react';
import { useState } from 'react';
import Nav from '../components/Nav';
import { type SharedData } from "@/types";

export default function CreatePharmacy() {
  const { auth } = usePage<SharedData>().props;
  const [form, setForm] = useState({
    name: '',
    address: '',
    phone: '',
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    router.post('/pharmacies', form);
  };

  return (
    <div className="min-h-screen bg-white text-black">
      <Nav />
      <div className="mx-auto max-w-3xl p-6">
        <h1 className="mb-4 text-2xl font-bold">Créer une pharmacie</h1>

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
              placeholder="Optionnel"
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
              placeholder="Optionnel"
            />
          </div>

          <div className="text-right">
            <button
              type="submit"
              className="rounded bg-green-600 px-4 py-2 text-white hover:bg-green-700"
            >
              Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
