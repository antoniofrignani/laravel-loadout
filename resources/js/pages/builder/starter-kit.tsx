import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Layers, ShieldCheck } from 'lucide-react';

import StarterKitController from '@/actions/App/Http/Controllers/Builder/StarterKitController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    project as builderProject,
    runtime as builderRuntime,
    starter as builderStarter,
} from '@/routes/builder';

type Values = {
    frontend_stack: string;
    authentication: string;
    teams: boolean;
    dark_mode: string;
};

type Props = {
    values: Values;
};

export default function StarterKit({ values }: Props) {
    return (
        <>
            <Head title="Starter Kit" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
                <header className="flex flex-col gap-2">
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>Step 2</span>
                        <span>/</span>
                        <span>Starter kit</span>
                    </div>
                    <div className="max-w-3xl">
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Choose the application shell
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Pick the frontend stack, authentication baseline,
                            and UI assumptions that shape the generated starter.
                        </p>
                    </div>
                </header>

                <section className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Starter configuration</CardTitle>
                            <CardDescription>
                                React/Inertia with Laravel authentication is the
                                default because it matches this app scaffold.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                className="grid gap-5"
                                {...StarterKitController.update.form()}
                            >
                                {({ errors, processing }) => (
                                    <>
                                        <div className="grid gap-4 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label>Frontend stack</Label>
                                                <Select
                                                    defaultValue={
                                                        values.frontend_stack
                                                    }
                                                    name="frontend_stack"
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="react-inertia">
                                                            React + Inertia
                                                        </SelectItem>
                                                        <SelectItem value="vue-inertia">
                                                            Vue + Inertia
                                                        </SelectItem>
                                                        <SelectItem value="livewire">
                                                            Livewire
                                                        </SelectItem>
                                                        <SelectItem value="blade">
                                                            Blade only
                                                        </SelectItem>
                                                        <SelectItem value="api">
                                                            API only
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                {errors.frontend_stack && (
                                                    <p className="text-sm text-destructive">
                                                        {errors.frontend_stack}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="grid gap-2">
                                                <Label>Authentication</Label>
                                                <Select
                                                    defaultValue={
                                                        values.authentication
                                                    }
                                                    name="authentication"
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="laravel-auth">
                                                            Laravel auth
                                                        </SelectItem>
                                                        <SelectItem value="none">
                                                            None
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                                {errors.authentication && (
                                                    <p className="text-sm text-destructive">
                                                        {errors.authentication}
                                                    </p>
                                                )}
                                            </div>

                                            <div className="grid gap-2">
                                                <Label>Team support</Label>
                                                <Select
                                                    defaultValue={
                                                        values.teams ? '1' : '0'
                                                    }
                                                    name="teams"
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="0">
                                                            No teams
                                                        </SelectItem>
                                                        <SelectItem value="1">
                                                            Include team-ready
                                                            notes
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="grid gap-2">
                                                <Label>Dark mode</Label>
                                                <Select
                                                    defaultValue={
                                                        values.dark_mode
                                                    }
                                                    name="dark_mode"
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="system">
                                                            Follow system
                                                        </SelectItem>
                                                        <SelectItem value="enabled">
                                                            Enabled
                                                        </SelectItem>
                                                        <SelectItem value="disabled">
                                                            Disabled
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>

                                        <div className="flex flex-wrap gap-2">
                                            <Button asChild variant="outline">
                                                <Link href={builderProject.url()}>
                                                    <ArrowLeft className="size-4" />
                                                    Back
                                                </Link>
                                            </Button>
                                            <Button disabled={processing}>
                                                Save and continue
                                                <ArrowRight className="size-4" />
                                            </Button>
                                        </div>
                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    <Card className="h-fit rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Layers className="size-5" />
                                Generated impact
                            </CardTitle>
                            <CardDescription>
                                These choices feed the previewed files.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <div className="rounded-md bg-muted p-3">
                                <ShieldCheck className="mb-2 size-4" />
                                Authentication and frontend choices are
                                summarized in README and determine frontend
                                package assumptions.
                            </div>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </>
    );
}

StarterKit.layout = {
    breadcrumbs: [
        {
            title: 'Builder',
            href: builderProject.url(),
        },
        {
            title: 'Starter',
            href: builderStarter.url(),
        },
        {
            title: 'Runtime',
            href: builderRuntime.url(),
        },
    ],
};
