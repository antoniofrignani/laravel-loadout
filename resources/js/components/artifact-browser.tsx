import { FileCode2, Files } from 'lucide-react';
import { useState } from 'react';

import { ArtifactContent } from '@/components/artifact-content';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { cn } from '@/lib/utils';

type Artifact = {
    name: string;
    language: string;
    content: string;
};

type Props = {
    artifacts: Artifact[];
    maxHeightClassName?: string;
};

export function ArtifactBrowser({
    artifacts,
    maxHeightClassName = 'max-h-[620px]',
}: Props) {
    const [selectedArtifactName, setSelectedArtifactName] = useState(
        artifacts[0]?.name ?? '',
    );
    const selectedArtifact =
        artifacts.find((artifact) => artifact.name === selectedArtifactName) ??
        artifacts[0];

    if (!selectedArtifact) {
        return null;
    }

    return (
        <div className="grid min-w-0 gap-4 xl:grid-cols-[190px_minmax(0,1fr)]">
            <nav
                aria-label="Generated files"
                className="flex gap-1 overflow-x-auto rounded-lg border bg-card p-2 xl:flex-col xl:overflow-visible"
            >
                <div className="hidden px-2 pb-2 xl:block">
                    <div className="flex items-center gap-2 text-sm font-medium">
                        <Files className="size-4 text-muted-foreground" />
                        Files in this loadout
                    </div>
                    <p className="mt-1 text-xs text-muted-foreground">
                        {artifacts.length} files ready to inspect
                    </p>
                </div>
                {artifacts.map((artifact) => {
                    const isSelected = artifact.name === selectedArtifact.name;

                    return (
                        <Button
                            aria-current={isSelected ? 'page' : undefined}
                            className={cn(
                                'h-auto min-w-fit justify-start px-2.5 py-2 text-left xl:w-full',
                                isSelected &&
                                    'bg-accent text-accent-foreground shadow-none',
                            )}
                            key={artifact.name}
                            onClick={() => setSelectedArtifactName(artifact.name)}
                            size="sm"
                            type="button"
                            variant="ghost"
                        >
                            <FileCode2 className="size-4 text-muted-foreground" />
                            <span className="font-mono text-xs">
                                {artifact.name}
                            </span>
                        </Button>
                    );
                })}
            </nav>

            <Card className="min-w-0 rounded-lg">
                <CardHeader className="flex-row items-start justify-between gap-4">
                    <div className="min-w-0 space-y-1">
                        <CardTitle className="truncate font-mono text-base">
                            {selectedArtifact.name}
                        </CardTitle>
                        <CardDescription>
                            {lineCount(selectedArtifact.content)} lines shaped
                            by your selections.
                        </CardDescription>
                    </div>
                    <Badge className="shrink-0" variant="secondary">
                        {selectedArtifact.language}
                    </Badge>
                </CardHeader>
                <CardContent>
                    <ArtifactContent
                        artifact={selectedArtifact}
                        maxHeightClassName={maxHeightClassName}
                    />
                </CardContent>
            </Card>
        </div>
    );
}

function lineCount(content: string): number {
    return content === '' ? 0 : content.split('\n').length;
}
