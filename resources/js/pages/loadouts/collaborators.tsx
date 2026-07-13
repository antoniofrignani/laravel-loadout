import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, Shield, Trash2, Users } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as loadoutsIndex } from '@/routes/loadouts';
import {
    destroy,
    store,
} from '@/routes/loadouts/collaborators';

type Loadout = {
    id: number;
    title: string;
    slug: string;
};

type Collaborator = {
    id: number;
    role: 'viewer' | 'editor';
    user: {
        id: number;
        name: string;
        email: string;
    };
};

type Props = {
    loadout: Loadout;
    collaborators: Collaborator[];
};

export default function LoadoutCollaborators({
    loadout,
    collaborators,
}: Props) {
    return (
        <>
            <Head title={`Collaborators - ${loadout.title}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="rounded-2xl border bg-card p-6 md:p-8">
                    <Button asChild variant="ghost" className="-ml-3 mb-4">
                        <Link href={loadoutsIndex()}>
                            <ArrowLeft className="size-4" />
                            Back to loadouts
                        </Link>
                    </Button>
                    <div className="flex flex-wrap items-center gap-2">
                        <Badge variant="secondary">
                            <Users className="size-3.5" />
                            Collaborators
                        </Badge>
                    </div>
                    <h1 className="mt-4 max-w-3xl text-3xl font-semibold tracking-tight md:text-4xl">
                        Manage access for {loadout.title}
                    </h1>
                    <p className="mt-3 max-w-2xl text-muted-foreground">
                        Add registered Laravel Loadout users as viewers or
                        editors. Editors will be able to modify shared loadout
                        choices once edit permissions are wired into the builder.
                    </p>
                </header>

                <section className="grid gap-6 lg:grid-cols-[380px_minmax(0,1fr)]">
                    <Card className="h-fit rounded-xl">
                        <CardHeader>
                            <CardTitle>Add collaborator</CardTitle>
                            <CardDescription>
                                Enter the email address of an existing account.
                                Re-adding a user updates their role.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                {...store.form(loadout.slug)}
                                resetOnSuccess
                                className="space-y-4"
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <label className="grid gap-2">
                                            <span className="text-sm font-medium">
                                                Email
                                            </span>
                                            <Input
                                                type="email"
                                                name="email"
                                                placeholder="teammate@example.com"
                                            />
                                            {errors.email && (
                                                <span className="text-sm text-destructive">
                                                    {errors.email}
                                                </span>
                                            )}
                                        </label>

                                        <label className="grid gap-2">
                                            <span className="text-sm font-medium">
                                                Role
                                            </span>
                                            <Select
                                                name="role"
                                                defaultValue="viewer"
                                            >
                                                <SelectTrigger className="w-full">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="viewer">
                                                        Viewer
                                                    </SelectItem>
                                                    <SelectItem value="editor">
                                                        Editor
                                                    </SelectItem>
                                                </SelectContent>
                                            </Select>
                                            {errors.role && (
                                                <span className="text-sm text-destructive">
                                                    {errors.role}
                                                </span>
                                            )}
                                        </label>

                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing
                                                ? 'Adding...'
                                                : 'Add collaborator'}
                                        </Button>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    <Card className="rounded-xl">
                        <CardHeader>
                            <CardTitle>Current collaborators</CardTitle>
                            <CardDescription>
                                People with explicit access to this loadout.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {collaborators.length > 0 ? (
                                collaborators.map((collaborator) => (
                                    <div
                                        key={collaborator.id}
                                        className="flex flex-wrap items-center justify-between gap-3 rounded-lg border p-4"
                                    >
                                        <div>
                                            <div className="font-medium">
                                                {collaborator.user.name}
                                            </div>
                                            <div className="text-sm text-muted-foreground">
                                                {collaborator.user.email}
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge
                                                variant={
                                                    collaborator.role ===
                                                    'editor'
                                                        ? 'secondary'
                                                        : 'outline'
                                                }
                                            >
                                                <Shield className="size-3.5" />
                                                {collaborator.role}
                                            </Badge>
                                            <Form
                                                {...destroy.form({
                                                    loadout: loadout.slug,
                                                    collaborator:
                                                        collaborator.id,
                                                })}
                                            >
                                                {({ processing }) => (
                                                    <Button
                                                        type="submit"
                                                        variant="ghost"
                                                        size="icon"
                                                        disabled={processing}
                                                        aria-label={`Remove ${collaborator.user.name}`}
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                )}
                                            </Form>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                <div className="rounded-lg border border-dashed p-6 text-sm text-muted-foreground">
                                    No collaborators yet.
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

LoadoutCollaborators.layout = {
    breadcrumbs: [
        {
            title: 'My Loadouts',
            href: loadoutsIndex(),
        },
        {
            title: 'Collaborators',
            href: loadoutsIndex(),
        },
    ],
};
