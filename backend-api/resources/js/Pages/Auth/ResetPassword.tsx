import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';
import { Card, TextInput, Label, Button, Alert } from 'flowbite-react';

interface Props {
    email: string;
    token: string;
}

export default function ResetPassword({ email, token }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/reset-password');
    };

    return (
        <AppLayout>
            <Head title="Reset Password" />
            <div className="flex items-center justify-center px-4">
                <Card className="w-full max-w-md rounded-xl shadow-md">
                    <div className="text-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">AlgoDrill</h2>
                        <p className="mt-2 text-gray-600 dark:text-gray-400">Reset your password</p>
                    </div>

                    {errors.password && (
                        <Alert color="failure" className="mb-4">
                            {errors.password}
                        </Alert>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <Label htmlFor="email" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Email</Label>
                            <TextInput
                                id="email"
                                type="email"
                                value={data.email}
                                readOnly
                                className="bg-gray-50 dark:bg-gray-700"
                            />
                        </div>

                        <div>
                            <Label htmlFor="password" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Password</Label>
                            <TextInput
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                color={errors.password ? 'failure' : 'gray'}
                                required
                            />
                            {errors.password && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.password}</p>}
                        </div>

                        <div>
                            <Label htmlFor="password_confirmation" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Confirm Password</Label>
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                required
                            />
                        </div>

                        <Button type="submit" disabled={processing} color="blue" className="w-full">
                            {processing ? 'Resetting...' : 'Reset Password'}
                        </Button>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
