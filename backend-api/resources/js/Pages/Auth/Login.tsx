import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';
import { Card, TextInput, Label, Button, Checkbox, Alert } from 'flowbite-react';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <AppLayout>
            <Head title="Login" />
            <div className="flex items-center justify-center px-4">
                <Card className="w-full max-w-md rounded-xl shadow-md">
                    <div className="text-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">AlgoDrill</h2>
                        <p className="mt-2 text-gray-600 dark:text-gray-400">Sign in to your account</p>
                    </div>

                    {errors.email && (
                        <Alert color="failure" className="mb-4">
                            {errors.email}
                        </Alert>
                    )}

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
                                onChange={(e) => setData('email', e.target.value)}
                                color={errors.email ? 'failure' : 'gray'}
                                required
                            />
                            {errors.email && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.email}</p>}
                        </div>

                        <div>
                            <Label htmlFor="password" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Password</Label>
                            <div className="relative">
                                <TextInput
                                    id="password"
                                    type={showPassword ? 'text' : 'password'}
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    color={errors.password ? 'failure' : 'gray'}
                                    required
                                    className="pr-10"
                                />
                                {errors.password && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.password}</p>}
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-2.5 bottom-2.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                                    aria-label={showPassword ? 'Hide password' : 'Show password'}
                                >
                                    {showPassword ? (
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    ) : (
                                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    )}
                                </button>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remember"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                            />
                            <Label htmlFor="remember" className="flex text-sm font-medium text-gray-900 dark:text-white">
                                Remember me
                            </Label>
                        </div>

                        <div className="flex items-center justify-between">
                            <Button type="submit" disabled={processing} color="blue" className="w-full">
                                {processing ? 'Logging in...' : 'Login'}
                            </Button>
                        </div>

                        <div className="text-center">
                            <Link href="/forgot-password" className="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                Forgot password?
                            </Link>
                        </div>

                        <div className="text-center text-sm text-gray-600 dark:text-gray-400">
                            Don't have an account?{' '}
                            <Link href="/register" className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                Sign up
                            </Link>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
