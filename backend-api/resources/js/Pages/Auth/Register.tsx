import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
        preferred_languages: [] as string[],
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/register');
    };

    const toggleLanguage = (lang: string) => {
        const current = data.preferred_languages || [];
        if (current.includes(lang)) {
            setData('preferred_languages', current.filter((l) => l !== lang));
        } else {
            setData('preferred_languages', [...current, lang]);
        }
    };

    return (
        <AppLayout>
            <div className="max-w-md mx-auto mt-8">
                <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
                    <h2 className="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Register</h2>

                    <form onSubmit={submit}>
                        <div className="mb-4">
                            <label htmlFor="name" className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                                Name
                            </label>
                            <input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required
                            />
                            {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                        </div>

                        <div className="mb-4">
                            <label htmlFor="username" className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                                Username (optional)
                            </label>
                            <input
                                id="username"
                                type="text"
                                value={data.username}
                                onChange={(e) => setData('username', e.target.value)}
                                className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            />
                            {errors.username && <p className="text-red-500 text-xs mt-1">{errors.username}</p>}
                        </div>

                        <div className="mb-4">
                            <label htmlFor="email" className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required
                            />
                            {errors.email && <p className="text-red-500 text-xs mt-1">{errors.email}</p>}
                        </div>

                        <div className="mb-4">
                            <label htmlFor="password" className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                                Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required
                            />
                            {errors.password && <p className="text-red-500 text-xs mt-1">{errors.password}</p>}
                        </div>

                        <div className="mb-4">
                            <label htmlFor="password_confirmation" className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                                Confirm Password
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-300 dark:bg-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                required
                            />
                        </div>

                        <div className="mb-6">
                            <label className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">
                                Preferred Languages (optional)
                            </label>
                            <div className="flex gap-2">
                                {['javascript', 'python', 'php'].map((lang) => (
                                    <label key={lang} className="flex items-center">
                                        <input
                                            type="checkbox"
                                            checked={data.preferred_languages?.includes(lang)}
                                            onChange={() => toggleLanguage(lang)}
                                            className="mr-1"
                                        />
                                        <span className="text-sm text-gray-700 dark:text-gray-300 capitalize">{lang}</span>
                                    </label>
                                ))}
                            </div>
                        </div>

                        <div className="flex items-center justify-between">
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                            >
                                {processing ? 'Registering...' : 'Register'}
                            </button>
                            <a href="/login" className="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
                                Already have an account?
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}

