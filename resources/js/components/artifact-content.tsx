import type { ReactNode } from 'react';

import JsonViewer from '@/components/8starlabs-ui/json-viewer';
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

    return <HighlightedArtifact artifact={artifact} maxHeightClassName={maxHeightClassName} />;
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
    return (
        <pre
            className={`${maxHeightClassName} overflow-auto rounded-md bg-zinc-950 p-4 font-mono text-xs leading-relaxed text-zinc-100`}
        >
            <code className="block">
                {artifact.content.split('\n').map((line, index) => (
                    <span className="block min-h-[1.25rem]" key={`${index}-${line}`}>
                        {highlightLine(artifact.language, line)}
                    </span>
                ))}
            </code>
        </pre>
    );
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
