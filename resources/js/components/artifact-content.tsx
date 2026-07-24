import { ChevronRight } from 'lucide-react';
import { useMemo, useState } from 'react';
import type { ReactNode } from 'react';

import JsonViewer from '@/components/8starlabs-ui/json-viewer';
import { CopyButton } from '@/components/8starlabs-ui/ui/copy-button';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
type Props = {
    artifact: {
        name: string;
        language: string;
        content: string;
    };
    maxHeightClassName?: string;
};

export function ArtifactContent({
    artifact,
    maxHeightClassName = 'max-h-[520px]',
}: Props) {
    const data = parseJsonArtifact(artifact);

    if (data) {
        return (
            <JsonViewer
                data={data}
                title={artifact.name}
                defaultExpanded={2}
                showLineNumbers
                showColorIndent
                collapseOn="click"
                truncation={{
                    enabled: false,
                }}
                className={maxHeightClassName}
            />
        );
    }

    return (
        <HighlightedArtifact
            artifact={artifact}
            key={artifact.name}
            maxHeightClassName={maxHeightClassName}
        />
    );
}

function parseJsonArtifact(artifact: Props['artifact']) {
    if (artifact.language !== 'json' && !artifact.name.endsWith('.json')) {
        return null;
    }

    try {
        const parsed = JSON.parse(artifact.content) as unknown;

        return parsed && typeof parsed === 'object'
            ? (parsed as Record<string, unknown>)
            : null;
    } catch {
        return null;
    }
}

function HighlightedArtifact({
    artifact,
    maxHeightClassName,
}: Required<Props>) {
    const [collapsedSections, setCollapsedSections] = useState<Set<number>>(
        new Set(),
    );
    const lines = useMemo(() => artifact.content.split('\n'), [artifact.content]);
    const collapsibleSections = useMemo(
        () => findCollapsibleSections(artifact.language, lines),
        [artifact.language, lines],
    );
    const visibleLines = useMemo(
        () =>
            visibleArtifactLines(
                artifact.language,
                lines,
                collapsibleSections,
                collapsedSections,
            ),
        [artifact.language, lines, collapsibleSections, collapsedSections],
    );
    const canCollapse = collapsibleSections.size > 0;

    return (
        <div className="overflow-hidden rounded-md border bg-zinc-950 font-mono text-xs text-zinc-100">
            <div className="flex items-center justify-between gap-2 border-b border-zinc-800 bg-zinc-900/80 p-2">
                <span className="min-w-0 truncate px-2 text-zinc-400">
                    {artifact.name}
                </span>
                <div className="flex shrink-0 items-center overflow-hidden rounded-md border border-zinc-700 bg-zinc-900">
                    {canCollapse && (
                        <>
                            <Button
                                className="h-7 rounded-none px-2 text-xs hover:bg-zinc-800"
                                onClick={() => setCollapsedSections(new Set())}
                                size="sm"
                                type="button"
                                variant="ghost"
                            >
                                Expand all
                            </Button>
                            <Separator
                                className="h-4 bg-zinc-700"
                                orientation="vertical"
                            />
                            <Button
                                className="h-7 rounded-none px-2 text-xs hover:bg-zinc-800"
                                onClick={() =>
                                    setCollapsedSections(
                                        new Set(collapsibleSections),
                                    )
                                }
                                size="sm"
                                type="button"
                                variant="ghost"
                            >
                                Collapse all
                            </Button>
                            <Separator
                                className="h-4 bg-zinc-700"
                                orientation="vertical"
                            />
                        </>
                    )}
                    <CopyButton
                        className="static size-7 rounded-none bg-transparent text-zinc-100 hover:bg-zinc-800 hover:opacity-100 focus-visible:opacity-100"
                        value={artifact.content}
                    />
                </div>
            </div>
            <pre className={`${maxHeightClassName} overflow-auto p-4 leading-relaxed`}>
                <code className="block">
                    {visibleLines.map(({ index, line }) => {
                        const isCollapsible = collapsibleSections.has(index);
                        const isCollapsed = collapsedSections.has(index);

                        return (
                            <span
                                className="block min-h-[1.25rem]"
                                key={`${index}-${line}`}
                            >
                                {isCollapsible ? (
                                    <button
                                        className="inline-flex cursor-pointer items-center text-left hover:text-white"
                                        onClick={() =>
                                            setCollapsedSections((sections) => {
                                                const nextSections = new Set(sections);

                                                if (nextSections.has(index)) {
                                                    nextSections.delete(index);
                                                } else {
                                                    nextSections.add(index);
                                                }

                                                return nextSections;
                                            })
                                        }
                                        type="button"
                                    >
                                        <ChevronRight
                                            className={cn(
                                                'mr-1 size-3 shrink-0 transition-transform',
                                                !isCollapsed && 'rotate-90',
                                            )}
                                        />
                                        {highlightLine(artifact.language, line)}
                                    </button>
                                ) : (
                                    highlightLine(artifact.language, line)
                                )}
                            </span>
                        );
                    })}
                </code>
            </pre>
        </div>
    );
}

function findCollapsibleSections(language: string, lines: string[]): Set<number> {
    const sections = new Set<number>();

    lines.forEach((line, index) => {
        if (language === 'yaml' && /^\S[^:#]*:\s*(?:#.*)?$/.test(line)) {
            sections.add(index);
        }

    });

    return sections;
}

function visibleArtifactLines(
    language: string,
    lines: string[],
    sections: Set<number>,
    collapsedSections: Set<number>,
): Array<{ index: number; line: string }> {
    if (collapsedSections.size === 0) {
        return lines.map((line, index) => ({ index, line }));
    }

    if (language !== 'yaml') {
        return lines.map((line, index) => ({ index, line }));
    }

    let isCollapsed = false;

    return lines.flatMap((line, index) => {
        if (sections.has(index)) {
            isCollapsed = collapsedSections.has(index);

            return [{ index, line }];
        }

        return isCollapsed ? [] : [{ index, line }];
    });
}

function highlightLine(language: string, line: string): ReactNode {
    switch (language) {
        case 'yaml':
            return highlightYamlLine(line);
        case 'dotenv':
            return highlightDotenvLine(line);
        case 'markdown':
            return highlightMarkdownLine(line);
        default:
            return line;
    }
}

function highlightYamlLine(line: string): ReactNode {
    if (line.trimStart().startsWith('#')) {
        return <span className="text-zinc-500">{line}</span>;
    }

    const match = line.match(/^(\s*)([^:#][^:]*?)(:)(.*)$/);

    if (!match) {
        return highlightYamlValue(line);
    }

    return (
        <>
            {match[1]}
            <span className="text-sky-300">{match[2]}</span>
            <span className="text-zinc-500">{match[3]}</span>
            {highlightYamlValue(match[4])}
        </>
    );
}

function highlightYamlValue(value: string): ReactNode {
    const commentIndex = value.search(/\s#/);
    const content = commentIndex === -1 ? value : value.slice(0, commentIndex);
    const comment = commentIndex === -1 ? '' : value.slice(commentIndex);
    const trimmed = content.trim();

    let highlightedContent: ReactNode = content;

    if (/^(true|false|null|~)$/i.test(trimmed)) {
        highlightedContent = <span className="text-violet-300">{content}</span>;
    } else if (/^-?\d+(?:\.\d+)?$/.test(trimmed)) {
        highlightedContent = <span className="text-amber-300">{content}</span>;
    } else if (/^['"].*['"]$/.test(trimmed)) {
        highlightedContent = <span className="text-emerald-300">{content}</span>;
    }

    return (
        <>
            {highlightedContent}
            {comment && <span className="text-zinc-500">{comment}</span>}
        </>
    );
}

function highlightDotenvLine(line: string): ReactNode {
    if (line.startsWith('#')) {
        return <span className="text-zinc-500">{line}</span>;
    }

    const match = line.match(/^(export\s+)?([A-Z][A-Z0-9_]*)(=)(.*)$/i);

    if (!match) {
        return line;
    }

    return (
        <>
            {match[1] && <span className="text-violet-300">{match[1]}</span>}
            <span className="text-sky-300">{match[2]}</span>
            <span className="text-zinc-500">{match[3]}</span>
            <span className="text-emerald-300">{match[4]}</span>
        </>
    );
}

function highlightMarkdownLine(line: string): ReactNode {
    if (/^```/.test(line)) {
        return <span className="text-violet-300">{line}</span>;
    }

    const heading = line.match(/^(#{1,6})(\s+)(.*)$/);

    if (heading) {
        return (
            <>
                <span className="text-violet-300">{heading[1]}</span>
                {heading[2]}
                <span className="font-semibold text-sky-200">
                    {heading[3]}
                </span>
            </>
        );
    }

    const listItem = line.match(/^(\s*)([-*+] |\d+\. )(.*)$/);

    if (listItem) {
        return (
            <>
                {listItem[1]}
                <span className="text-violet-300">{listItem[2]}</span>
                {highlightMarkdownInline(listItem[3])}
            </>
        );
    }

    return highlightMarkdownInline(line);
}

function highlightMarkdownInline(line: string): ReactNode {
    const parts = line.split(/(`[^`]+`|\*\*[^*]+\*\*|\[[^\]]+\]\([^)]*\))/g);

    return parts.map((part, index) => {
        if (/^`[^`]+`$/.test(part)) {
            return (
                <span className="text-emerald-300" key={index}>
                    {part}
                </span>
            );
        }

        if (/^\*\*[^*]+\*\*$/.test(part)) {
            return (
                <span className="font-semibold text-amber-200" key={index}>
                    {part}
                </span>
            );
        }

        if (/^\[[^\]]+\]\([^)]*\)$/.test(part)) {
            return (
                <span className="text-sky-300 underline decoration-sky-400/50" key={index}>
                    {part}
                </span>
            );
        }

        return <span key={index}>{part}</span>;
    });
}
