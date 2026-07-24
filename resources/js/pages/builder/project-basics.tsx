import { Form, Head } from '@inertiajs/react';
import { Boxes, Info } from 'lucide-react';

import ProjectBasicsController from '@/actions/App/Http/Controllers/Builder/ProjectBasicsController';
import { BuilderStepNavigation } from '@/components/builder-step-navigation';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { project as builderProject } from '@/routes/builder';

type Values = {
    project_name: string;
    app_name: string;
    description: string | null;
    php_version: string;
    laravel_version: string;
    package_manager: string;
};

type Props = {
    values: Values;
};

export default function ProjectBasics({ values }: Props) {
    return (
        <>
            <Head title="Project Basics" />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 pb-24 md:p-6 md:pb-24">
                <header className="flex flex-col gap-2">
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>Step 1</span>
                        <span>/</span>
                        <span>Project basics</span>
                    </div>
                    <div className="max-w-3xl">
                        <h1 className="text-3xl font-semibold tracking-normal">
                            Give this stack a name
                        </h1>
                        <p className="mt-2 text-sm text-muted-foreground">
                            This is the identity carried into the README,
                            Composer metadata, and `.env.loadout`.
                        </p>
                    </div>
                </header>

                <section className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <Card className="rounded-lg">
                        <CardHeader>
                            <CardTitle>Project brief</CardTitle>
                            <CardDescription>
                                Write this for the person who will pick up the
                                loadout later, including future you.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Form
                                id="project-basics-form"
                                className="grid gap-5"
                                {...ProjectBasicsController.update.form()}
                            >
                                {({ errors }) => (
                                    <>
                                        <div className="grid gap-2">
                                            <Label htmlFor="project_name">
                                                Project name
                                            </Label>
                                            <Input
                                                id="project_name"
                                                name="project_name"
                                                defaultValue={
                                                    values.project_name
                                                }
                                                placeholder="Agency App Baseline"
                                            />
                                            {errors.project_name && (
                                                <p className="text-sm text-destructive">
                                                    {errors.project_name}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="app_name">
                                                App name
                                            </Label>
                                            <Input
                                                id="app_name"
                                                name="app_name"
                                                defaultValue={values.app_name}
                                                placeholder="Acme Portal"
                                            />
                                            {errors.app_name && (
                                                <p className="text-sm text-destructive">
                                                    {errors.app_name}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="description">
                                                Description
                                            </Label>
                                            <Textarea
                                                id="description"
                                                name="description"
                                                defaultValue={
                                                    values.description ?? ''
                                                }
                                                placeholder="A solid Laravel baseline for client portals and internal tools."
                                            />
                                            {errors.description && (
                                                <p className="text-sm text-destructive">
                                                    {errors.description}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-4 md:grid-cols-3">
                                            <div className="grid gap-2">
                                                <Label>PHP version</Label>
                                                <Select
                                                    defaultValue={
                                                        values.php_version
                                                    }
                                                    name="php_version"
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="8.4">
                                                            PHP 8.4
                                                        </SelectItem>
                                                        <SelectItem value="8.5">
                                                            PHP 8.5
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="grid gap-2">
                                                <Label>Laravel version</Label>
                                                <Select
                                                    defaultValue={
                                                        values.laravel_version
                                                    }
                                                    name="laravel_version"
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="13.x">
                                                            Laravel 13.x
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>

                                            <div className="grid gap-2">
                                                <Label>Package manager</Label>
                                                <Select
                                                    defaultValue={
                                                        values.package_manager
                                                    }
                                                    name="package_manager"
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        <SelectItem value="yarn">
                                                            Yarn
                                                        </SelectItem>
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        </div>

                                    </>
                                )}
                            </Form>
                        </CardContent>
                    </Card>

                    <Card className="h-fit rounded-lg">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Boxes className="size-5" />
                                Builder path
                            </CardTitle>
                            <CardDescription>
                                The next screens will refine the stack before
                                generation.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm text-muted-foreground">
                            <div className="rounded-md bg-muted p-3">
                                <Info className="mb-2 size-4" />
                                This first step currently persists the project
                                metadata used by generated artifacts. Starter
                                kit and runtime steps come next.
                            </div>
                        </CardContent>
                    </Card>
                </section>

                <BuilderStepNavigation
                    step={1}
                    submitFormId="project-basics-form"
                />
            </div>
        </>
    );
}

ProjectBasics.layout = {
    breadcrumbs: [
        {
            title: 'Builder',
            href: builderProject.url(),
        },
        {
            title: 'Project',
            href: builderProject.url(),
        },
    ],
};
