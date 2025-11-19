import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';
import { Card, Button, Alert } from 'flowbite-react';

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
            <Head title="Verify Email" />
            <div className="flex items-center justify-center px-4">
                <Card className="w-full max-w-md rounded-xl shadow-md">
                    <div className="text-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">AlgoDrill</h2>
                        <p className="mt-2 text-gray-600 dark:text-gray-400">Verify Your Email</p>
                    </div>

                    <p className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on
                        the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                    </p>

                    {status === 'verification-link-sent' && (
                        <Alert color="success" className="mb-4">
                            A new verification link has been sent to the email address you provided during registration.
                        </Alert>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <Button type="submit" disabled={processing} color="blue" className="w-full">
                            {processing ? 'Sending...' : 'Resend Verification Email'}
                        </Button>
                    </form>

                    <div className="text-center mt-4">
                        <Link
                            href="/logout"
                            method="post"
                            className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Logout
                        </Link>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
