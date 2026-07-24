import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowRight } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { useSidebar } from '@/components/ui/sidebar';
import { cn } from '@/lib/utils';

type NavigationAction = {
    href: string;
    label: string;
    download?: boolean;
    external?: boolean;
};

type Props = {
    step: number;
    previous?: NavigationAction;
    next?: NavigationAction;
    submitFormId?: string;
    submitLabel?: string;
    isSubmitting?: boolean;
};

export function BuilderStepNavigation({
    step,
    previous,
    next,
    submitFormId,
    submitLabel = 'Save and continue',
    isSubmitting = false,
}: Props) {
    const { state } = useSidebar();

    return (
        <footer
            className={cn(
                'fixed inset-x-0 bottom-0 z-30 border-t bg-background/95 shadow-[0_-8px_24px_-16px_hsl(var(--foreground)/0.35)] backdrop-blur supports-[backdrop-filter]:bg-background/80 md:left-(--sidebar-width)',
                state === 'collapsed' &&
                    'md:left-[calc(var(--sidebar-width-icon)+(--spacing(4)))]',
            )}
        >
            <div className="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-3 md:px-6">
                <div className="min-w-28">
                    {previous && (
                        <Button asChild variant="outline">
                            <Link href={previous.href}>
                                <ArrowLeft className="size-4" />
                                {previous.label}
                            </Link>
                        </Button>
                    )}
                </div>

                <span className="hidden text-sm text-muted-foreground sm:inline">
                    Step {step} of 6
                </span>

                <div className="flex min-w-28 justify-end">
                    {submitFormId ? (
                        <Button
                            disabled={isSubmitting}
                            form={submitFormId}
                            type="submit"
                        >
                            {isSubmitting ? 'Saving...' : submitLabel}
                            <ArrowRight className="size-4" />
                        </Button>
                    ) : next ? (
                        <Button asChild>
                            {next.external ? (
                                <a download={next.download} href={next.href}>
                                    {next.label}
                                    <ArrowRight className="size-4" />
                                </a>
                            ) : (
                                <Link href={next.href}>
                                    {next.label}
                                    <ArrowRight className="size-4" />
                                </Link>
                            )}
                        </Button>
                    ) : null}
                </div>
            </div>
        </footer>
    );
}
