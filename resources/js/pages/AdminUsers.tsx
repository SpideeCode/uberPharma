import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';


export default function AdminUsers() {
  const { users } = usePage().props;
  const [newUser, setNewUser] = useState({
    name: '',
    email: '',
    password: '',
    role: 'client',
  });
  const [editRole, setEditRole] = useState<{ [key: number]: string }>({});

  const handleNewUserChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    setNewUser((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    router.post('/users', newUser, {
      onSuccess: () => setNewUser({ name: '', email: '', password: '', role: 'client' }),
    });
  };

  const handleRoleChange = (userId: number, role: string) => {
    setEditRole((prev) => ({ ...prev, [userId]: role }));
    router.post(`/users/${userId}/update-role`, { role }, { preserveScroll: true });
  };

  const handleDeleteUser = (userId: number) => {
    if (!confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) return;
    router.delete(`/users/${userId}`);
  };

  return (
    <div className="min-h-screen bg-white text-black">
 
      <div className="p-6">
        <h1 className="text-3xl font-bold mb-6">Gestion des utilisateurs</h1>

        {/* Formulaire création */}
        <form onSubmit={handleCreateUser} className="mb-8 rounded border border-gray-300 bg-gray-50 p-4">
          <h2 className="mb-4 text-xl font-semibold">Créer un nouvel utilisateur</h2>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
            <input
              type="text"
              name="name"
              placeholder="Nom"
              value={newUser.name}
              onChange={handleNewUserChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            />
            <input
              type="email"
              name="email"
              placeholder="Email"
              value={newUser.email}
              onChange={handleNewUserChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            />
            <input
              type="password"
              name="password"
              placeholder="Mot de passe"
              value={newUser.password}
              onChange={handleNewUserChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            />
            <select
              name="role"
              value={newUser.role}
              onChange={handleNewUserChange}
              className="rounded border border-gray-400 px-3 py-2 focus:ring focus:ring-gray-400 focus:outline-none"
              required
            >
              <option value="client">Client</option>
              <option value="pharmacy">Pharmacy</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <button type="submit" className="mt-4 rounded bg-black px-6 py-2 text-white hover:bg-gray-900">
            Créer
          </button>
        </form>

        {/* Tableau des utilisateurs */}
        <div className="hidden md:block">
          <table className="w-full border-collapse border border-gray-300">
            <thead>
              <tr className="bg-gray-100">
                <th className="border border-gray-300 px-4 py-2 text-left">ID</th>
                <th className="border border-gray-300 px-4 py-2 text-left">Nom</th>
                <th className="border border-gray-300 px-4 py-2 text-left">Email</th>
                <th className="border border-gray-300 px-4 py-2 text-left">Rôle</th>
                <th className="border border-gray-300 px-4 py-2 text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              {users.map((user: any) => (
                <tr key={user.id} className="hover:bg-gray-50">
                  <td className="border border-gray-300 px-4 py-2">{user.id}</td>
                  <td className="border border-gray-300 px-4 py-2">{user.name}</td>
                  <td className="border border-gray-300 px-4 py-2">{user.email}</td>
                  <td className="border border-gray-300 px-4 py-2">
                    <select
                      value={editRole[user.id] ?? user.role}
                      onChange={(e) => handleRoleChange(user.id, e.target.value)}
                      className="rounded border border-gray-400 px-2 py-1 focus:ring focus:ring-gray-400 focus:outline-none"
                    >
                      <option value="client">Client</option>
                      <option value="pharmacy">Pharmacy</option>
                      <option value="admin">Admin</option>
                    </select>
                  </td>
                  <td className="space-x-2 border border-gray-300 px-4 py-2 text-center">
                    <button
                      onClick={() => handleDeleteUser(user.id)}
                      className="rounded bg-red-600 px-3 py-1 text-white hover:bg-red-700"
                      type="button"
                    >
                      Supprimer
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Mode mobile */}
        <div className="space-y-4 md:hidden">
          {users.map((user: any) => (
            <div key={user.id} className="rounded border border-gray-300 bg-gray-50 p-4 shadow-sm">
              <p>
                <span className="font-semibold">ID :</span> {user.id}
              </p>
              <p>
                <span className="font-semibold">Nom :</span> {user.name}
              </p>
              <p>
                <span className="font-semibold">Email :</span> {user.email}
              </p>

              <div className="mt-2">
                <label className="mb-1 block text-sm font-semibold">Rôle</label>
                <select
                  value={editRole[user.id] ?? user.role}
                  onChange={(e) => handleRoleChange(user.id, e.target.value)}
                  className="w-full rounded border border-gray-400 px-2 py-1 focus:ring focus:ring-gray-400 focus:outline-none"
                >
                  <option value="client">Client</option>
                  <option value="pharmacy">Pharmacy</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              <button
                onClick={() => handleDeleteUser(user.id)}
                className="mt-4 w-full rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                type="button"
              >
                Supprimer
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
