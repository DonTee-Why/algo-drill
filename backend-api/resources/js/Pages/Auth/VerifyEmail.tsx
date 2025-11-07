import { Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';

interface Props {
    status?: string;
}

export default function VerifyEmail({ status }: Props) {
    const { post, processing } = useForm({});

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/email/verification-notification');
    };

    return (
        <AppLayout>
            <div className="max-w-md mx-auto mt-8">
                <div className="bg-white dark:bg-gray-800 shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
                    <h2 className="text-2xl font-bold mb-6 text-gray-900 dark:text-white">Verify Your Email</h2>

                    <div className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                    </div>

                    {status === 'verification-link-sent' && (
                        <div className="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            A new verification link has been sent to the email address you provided during registration.
                        </div>
                    )}

                    <form onSubmit={submit} className="mb-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                        >
                            {processing ? 'Sending...' : 'Resend Verification Email'}
                        </button>
                    </form>

                    <div className="text-center">
                        <Link
                            href="/logout"
                            method="post"
                            className="text-sm text-blue-600 hover:text-blue-800"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

