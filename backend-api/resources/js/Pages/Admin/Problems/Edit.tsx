import { Head, useForm } from '@inertiajs/react';
import React from 'react';
import AdminLayout from '../../Layouts/AdminLayout';
import { Button, Label, TextInput, Textarea, Select, Checkbox, Card } from 'flowbite-react';
import { route } from 'ziggy-js';

interface Signature {
    lang: string;
    function_name: string;
    params: string;
    returns: string;
}

interface Test {
    input: string;
    expected: string;
    is_edge: boolean;
    weight: number;
}

interface Problem {
    id: string;
    title: string;
    slug: string;
    difficulty: string;
    tags: string[];
    constraints: string[];
    description_md: string;
    is_premium: boolean;
    signatures: Array<{
        lang: string;
        function_name: string;
        params: Array<{ name: string; type: string }>;
        returns: { type: string };
    }>;
    tests: Array<{
        input: Record<string, any>;
        expected: any;
        is_edge: boolean;
        weight: number;
    }>;
}

interface Props {
    auth?: {
        user?: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    problem: Problem | null;
}

export default function Edit({ auth, problem }: Props) {
    const isEditing = !!problem;

    const { data, setData, post, put, processing, errors, transform } = useForm({
        title: problem?.title || '',
        slug: problem?.slug || '',
        difficulty: problem?.difficulty || 'Easy',
        tags: problem?.tags.join(', ') || '',
        constraints: problem?.constraints.join('\n') || '',
        description_md: problem?.description_md || '',
        is_premium: problem?.is_premium || false,
        signatures: problem?.signatures.map(sig => ({
            lang: sig.lang,
            function_name: sig.function_name,
            params: JSON.stringify(sig.params, null, 2),
            returns: JSON.stringify(sig.returns, null, 2),
        })) || [{ lang: 'javascript', function_name: '', params: '[]', returns: '{}' }],
        tests: problem?.tests.map(test => ({
            input: JSON.stringify(test.input, null, 2),
            expected: JSON.stringify(test.expected, null, 2),
            is_edge: test.is_edge,
            weight: test.weight,
        })) || [{ input: '{}', expected: '', is_edge: false, weight: 1 }],
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();

        // Transform data: convert strings to proper formats for backend
        const transformedData = {
            ...data,
            tags: data.tags.split(',').map(t => t.trim()).filter(t => t),
            constraints: data.constraints.split('\n').map(c => c.trim()).filter(c => c),
            signatures: data.signatures.map(sig => ({
                lang: sig.lang,
                function_name: sig.function_name,
                params: JSON.parse(sig.params),
                returns: JSON.parse(sig.returns),
            })),
            tests: data.tests.map(test => ({
                input: JSON.parse(test.input),
                expected: JSON.parse(test.expected),
                is_edge: test.is_edge,
                weight: test.weight,
            })),
        };

        // Use transform to modify the data before sending
        transform(() => transformedData);

        if (isEditing) {
            put(route('admin.problems.update', problem.id));
        } else {
            post(route('admin.problems.store'));
        }
    };

    const addSignature = () => {
        setData('signatures', [
            ...data.signatures,
            { lang: 'javascript', function_name: '', params: '[]', returns: '{}' },
        ]);
    };

    const removeSignature = (index: number) => {
        setData('signatures', data.signatures.filter((_, i) => i !== index));
    };

    const updateSignature = (index: number, field: keyof Signature, value: string | boolean) => {
        const updated = [...data.signatures];
        updated[index] = { ...updated[index], [field]: value };
        setData('signatures', updated);
    };

    const addTest = () => {
        setData('tests', [
            ...data.tests,
            { input: '{}', expected: '', is_edge: false, weight: 1 },
        ]);
    };

    const removeTest = (index: number) => {
        setData('tests', data.tests.filter((_, i) => i !== index));
    };

    const updateTest = (index: number, field: keyof Test, value: string | boolean | number) => {
        const updated = [...data.tests];
        updated[index] = { ...updated[index], [field]: value };
        setData('tests', updated);
    };

    return (
        <AdminLayout auth={auth}>
            <Head title={isEditing ? 'Edit Problem' : 'Create Problem'} />
            <div className="w-full mx-auto px-8 py-8">
                <div className="mb-6">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                        {isEditing ? 'Edit Problem' : 'Create New Problem'}
                    </h1>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Basic Info */}
                    <Card>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            Basic Information
                        </h2>
                        <div className="space-y-4">
                            <div>
                                <Label htmlFor="title">Title</Label>
                                <TextInput
                                    id="title"
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    color={errors.title ? 'failure' : undefined}
                                />
                                {errors.title && <p className="mt-1 text-sm text-red-600">{errors.title}</p>}
                            </div>
                            <div>
                                <Label htmlFor="slug">Slug (leave empty to auto-generate)</Label>
                                <TextInput
                                    id="slug"
                                    value={data.slug}
                                    onChange={(e) => setData('slug', e.target.value)}
                                    color={errors.slug ? 'failure' : undefined}
                                />
                                {errors.slug && <p className="mt-1 text-sm text-red-600">{errors.slug}</p>}
                            </div>
                            <div>
                                <Label htmlFor="difficulty">Difficulty</Label>
                                <Select
                                    id="difficulty"
                                    value={data.difficulty}
                                    onChange={(e) => setData('difficulty', e.target.value)}
                                >
                                    <option value="Easy">Easy</option>
                                    <option value="Medium">Medium</option>
                                    <option value="Hard">Hard</option>
                                </Select>
                            </div>
                            <div className="flex items-center gap-2">
                                <Checkbox
                                    id="is_premium"
                                    checked={data.is_premium}
                                    onChange={(e) => setData('is_premium', e.target.checked)}
                                />
                                <Label htmlFor="is_premium">Premium Problem</Label>
                            </div>
                        </div>
                    </Card>

                    {/* Tags */}
                    <Card>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            Tags
                        </h2>
                        <div>
                            <Label htmlFor="tags">Tags (comma-separated)</Label>
                            <TextInput
                                id="tags"
                                value={data.tags}
                                onChange={(e) => setData('tags', e.target.value)}
                                placeholder="Array, String, Hash Table"
                                color={errors.tags ? 'failure' : undefined}
                            />
                            {errors.tags && <p className="mt-1 text-sm text-red-600">{errors.tags}</p>}
                        </div>
                    </Card>

                    {/* Constraints */}
                    <Card>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            Constraints
                        </h2>
                        <div>
                            <Label htmlFor="constraints">Constraints (one per line)</Label>
                            <Textarea
                                id="constraints"
                                rows={4}
                                value={data.constraints}
                                onChange={(e) => setData('constraints', e.target.value)}
                                placeholder="1 <= n <= 10^4&#10;0 <= nums[i] <= 100"
                                color={errors.constraints ? 'failure' : undefined}
                            />
                            {errors.constraints && <p className="mt-1 text-sm text-red-600">{errors.constraints}</p>}
                        </div>
                    </Card>

                    {/* Description */}
                    <Card>
                        <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4">
                            Description (Markdown)
                        </h2>
                        <div>
                            <Textarea
                                id="description_md"
                                rows={8}
                                value={data.description_md}
                                onChange={(e) => setData('description_md', e.target.value)}
                                color={errors.description_md ? 'failure' : undefined}
                            />
                            {errors.description_md && <p className="mt-1 text-sm text-red-600">{errors.description_md}</p>}
                        </div>
                    </Card>

                    {/* Signatures */}
                    <Card>
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                                Function Signatures
                            </h2>
                            <Button type="button" size="sm" onClick={addSignature} className="cursor-pointer">
                                Add Signature
                            </Button>
                        </div>
                        <div className="space-y-4">
                            {data.signatures.map((sig, index) => (
                                <div key={index} className="p-4 border rounded-lg dark:border-gray-600">
                                    <div className="flex items-center justify-between mb-3">
                                        <span className="font-semibold text-gray-700 dark:text-gray-300">
                                            Signature {index + 1}
                                        </span>
                                        {data.signatures.length > 1 && (
                                            <Button
                                                type="button"
                                                size="xs"
                                                color="red"
                                                onClick={() => removeSignature(index)}
                                                className="cursor-pointer"
                                            >
                                                Remove
                                            </Button>
                                        )}
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <Label>Language</Label>
                                            <Select
                                                value={sig.lang}
                                                onChange={(e) => updateSignature(index, 'lang', e.target.value)}
                                            >
                                                <option value="javascript">JavaScript</option>
                                                <option value="python">Python</option>
                                                <option value="php">PHP</option>
                                            </Select>
                                        </div>
                                        <div>
                                            <Label>Function Name</Label>
                                            <TextInput
                                                value={sig.function_name}
                                                onChange={(e) => updateSignature(index, 'function_name', e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <Label>Params (JSON)</Label>
                                            <Textarea
                                                rows={3}
                                                value={sig.params}
                                                onChange={(e) => updateSignature(index, 'params', e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <Label>Returns (JSON)</Label>
                                            <Textarea
                                                rows={3}
                                                value={sig.returns}
                                                onChange={(e) => updateSignature(index, 'returns', e.target.value)}
                                            />
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Card>

                    {/* Tests */}
                    <Card>
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                                Test Cases
                            </h2>
                            <Button type="button" size="sm" onClick={addTest} className="cursor-pointer">
                                Add Test
                            </Button>
                        </div>
                        <div className="space-y-4">
                            {data.tests.map((test, index) => (
                                <div key={index} className="p-4 border rounded-lg dark:border-gray-600">
                                    <div className="flex items-center justify-between mb-3">
                                        <span className="font-semibold text-gray-700 dark:text-gray-300">
                                            Test {index + 1}
                                        </span>
                                        {data.tests.length > 1 && (
                                            <Button
                                                type="button"
                                                size="xs"
                                                color="red"
                                                onClick={() => removeTest(index)}
                                                className="cursor-pointer"
                                            >
                                                Remove
                                            </Button>
                                        )}
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div>
                                            <Label>Input (JSON)</Label>
                                            <Textarea
                                                rows={3}
                                                value={test.input}
                                                onChange={(e) => updateTest(index, 'input', e.target.value)}
                                            />
                                        </div>
                                        <div>
                                            <Label>Expected (JSON)</Label>
                                            <Textarea
                                                rows={3}
                                                value={test.expected}
                                                onChange={(e) => updateTest(index, 'expected', e.target.value)}
                                            />
                                        </div>
                                        <div className="flex items-center gap-4">
                                            <div className="flex items-center gap-2">
                                                <Checkbox
                                                    checked={test.is_edge}
                                                    onChange={(e) => updateTest(index, 'is_edge', e.target.checked)}
                                                />
                                                <Label>Edge Case</Label>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <Label>Weight:</Label>
                                                <TextInput
                                                    type="number"
                                                    min="1"
                                                    value={test.weight}
                                                    onChange={(e) => updateTest(index, 'weight', parseInt(e.target.value))}
                                                    className="w-20"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Card>

                    {/* Submit */}
                    <div className="flex gap-4">
                        <Button type="submit" color="blue" disabled={processing} className="cursor-pointer">
                            {isEditing ? 'Update Problem' : 'Create Problem'}
                        </Button>
                        <Button
                            type="button"
                            color="gray"
                            onClick={() => window.history.back()}
                            className="cursor-pointer"
                        >
                            Cancel
                        </Button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

