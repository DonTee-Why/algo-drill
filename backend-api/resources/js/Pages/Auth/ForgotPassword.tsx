import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';
import { Card, TextInput, Label, Button, Alert } from 'flowbite-react';

interface Props {
    status?: string;
}

export default function ForgotPassword({ status }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <AppLayout>
            <Head title="Forgot Password" />
            <div className="flex items-center justify-center px-4">
                <Card className="w-full max-w-md rounded-xl shadow-md">
                    <div className="text-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">AlgoDrill</h2>
                        <p className="mt-2 text-gray-600 dark:text-gray-400">Forgot your password?</p>
                    </div>

                    <p className="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        No problem. Just let us know your email address and we will email you a password reset link.
                    </p>

                    {status && (
                        <Alert color="success" className="mb-4">
                            {status}
                        </Alert>
                    )}

                    {errors.email && (
                        <Alert color="failure" className="mb-4">
                            {errors.email}
                        </Alert>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <Label htmlFor="email" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Email</Label>
                            <TextInput
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                color={errors.email ? 'failure' : 'gray'}
                                required
                            />
                            {errors.email && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.email}</p>}
                        </div>

                        <div className="flex items-center justify-between">
                            <Button type="submit" disabled={processing} color="blue" className="w-full">
                                {processing ? 'Sending...' : 'Send Reset Link'}
                            </Button>
                        </div>

                        <div className="text-center">
                            <Link href="/login" className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                Back to login
                            </Link>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
