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

    return (
        <pre className={`${maxHeightClassName} overflow-auto rounded-md bg-zinc-950 p-4 text-xs leading-relaxed text-zinc-100`}>
            <code>{artifact.content}</code>
        </pre>
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
