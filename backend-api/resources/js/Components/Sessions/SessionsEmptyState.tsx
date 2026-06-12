import { Link } from '@inertiajs/react';
import React from 'react';
import { Card, Button } from 'flowbite-react';
import { Clock } from 'lucide-react';
import { route } from 'ziggy-js';

export default function SessionsEmptyState() {
    return (
        <Card className="dark:bg-gray-800">
            <div className="text-center py-12">
                <Clock className="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">No sessions yet</h3>
                <p className="text-gray-600 dark:text-gray-400 mb-6">
                    Start a new session by selecting a problem from the Problems page.
                </p>
                <Link href={route('problems.index')}>
                    <Button color="blue">Browse Problems</Button>
                </Link>
            </div>
        </Card>
    );
}
