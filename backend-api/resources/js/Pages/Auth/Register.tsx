import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import AppLayout from '../Layouts/App';
import React from 'react';
import { Card, TextInput, Label, Button, Checkbox, Alert } from 'flowbite-react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
        preferred_languages: [] as string[],
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false);

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

    const languages = ['javascript', 'python', 'php'];

    return (
        <AppLayout>
            <Head title="Register" />
            <div className="flex items-center justify-center px-4">
                <Card className="w-full max-w-md rounded-xl shadow-md">
                    <div className="text-center mb-6">
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">AlgoDrill</h2>
                        <p className="mt-2 text-gray-600 dark:text-gray-400">Create your account</p>
                    </div>

                    {(errors.name || errors.email || errors.password || errors.username) && (
                        <Alert color="failure" className="mb-4">
                            {errors.name || errors.email || errors.password || errors.username}
                        </Alert>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <Label htmlFor="name" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Name</Label>
                            <TextInput
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                color={errors.name ? 'failure' : 'gray'}
                                required
                            />
                            {errors.name && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.name}</p>}
                        </div>

                        <div>
                            <Label htmlFor="username" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Username (optional)</Label>
                            <TextInput
                                id="username"
                                type="text"
                                value={data.username}
                                onChange={(e) => setData('username', e.target.value)}
                                color={errors.username ? 'failure' : 'gray'}
                            />
                            {errors.username && <p className="mt-1 text-sm text-red-600 dark:text-red-400">{errors.username}</p>}
                        </div>

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

                        <div>
                            <Label htmlFor="password_confirmation" className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Confirm Password</Label>
                            <div className="relative">
                                <TextInput
                                    id="password_confirmation"
                                    type={showPasswordConfirmation ? 'text' : 'password'}
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    required
                                    className="pr-10"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPasswordConfirmation(!showPasswordConfirmation)}
                                    className="absolute right-2.5 bottom-2.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                                    aria-label={showPasswordConfirmation ? 'Hide password' : 'Show password'}
                                >
                                    {showPasswordConfirmation ? (
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

                        <div>
                            <Label className="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Preferred Languages (optional)</Label>
                            <div className="flex gap-4 mt-2">
                                {languages.map((lang) => (
                                    <div key={lang} className="flex items-center gap-2">
                                        <Checkbox
                                            id={`lang-${lang}`}
                                            checked={data.preferred_languages?.includes(lang)}
                                            onChange={() => toggleLanguage(lang)}
                                        />
                                        <Label htmlFor={`lang-${lang}`} className="flex capitalize text-sm font-medium text-gray-900 dark:text-white">
                                            {lang}
                                        </Label>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <Button type="submit" disabled={processing} color="blue" className="w-full">
                            {processing ? 'Registering...' : 'Register'}
                        </Button>

                        <div className="text-center text-sm text-gray-600 dark:text-gray-400">
                            Already have an account?{' '}
                            <Link href="/login" className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                Sign in
                            </Link>
                        </div>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
