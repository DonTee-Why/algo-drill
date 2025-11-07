import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <AppLayout>
            <div className="max-w-md mx-auto mt-8">
                <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
                    <h2 className="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Forgot Password</h2>

                    <div className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.
                    </div>

                    {status && (
                        <div className="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            {status}
                        </div>
                    )}

                    <form onSubmit={submit}>
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

                        <div className="flex items-center justify-between">
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                            >
                                {processing ? 'Sending...' : 'Send Reset Link'}
                            </button>
                            <a href="/login" className="inline-block align-baseline font-bold text-sm text-blue-600 hover:text-blue-800">
                                Back to login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}

