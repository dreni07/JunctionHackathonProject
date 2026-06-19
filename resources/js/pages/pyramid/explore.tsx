import { Head, Link } from '@inertiajs/react';
import { Database, FileUp, Table2 } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Column = {
    name: string;
    type: string;
};

type KnowledgeTable = {
    table: string;
    label: string;
    kind: 'ingested' | 'domain';
    row_count: number;
    columns: Column[];
    display_columns: string[];
    rows: Record<string, unknown>[];
};

type Props = {
    tables: KnowledgeTable[];
    totalTables: number;
    totalRows: number;
};

function formatCell(value: unknown): string {
    if (value === null || value === undefined) {
        return '—';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    const text = String(value);

    return text.length > 240 ? `${text.slice(0, 240)}…` : text;
}

export default function PyramidExplore({
    tables,
    totalTables,
    totalRows,
}: Props) {
    const ingestedTables = tables.filter((table) => table.kind === 'ingested');
    const domainTables = tables.filter((table) => table.kind === 'domain');

    return (
        <>
            <Head title="Pyramid knowledge" />

            <div className="mx-auto min-h-screen w-full max-w-6xl space-y-6 p-4 text-foreground md:p-8">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="space-y-2">
                        <div className="flex items-center gap-2">
                            <Database className="size-5 text-primary" />
                            <h1 className="text-2xl font-semibold tracking-tight">
                                Pyramid knowledge base
                            </h1>
                        </div>
                        <p className="max-w-2xl text-sm text-muted-foreground">
                            Every table extracted from Pyramid PDFs and related
                            operational records currently stored in the
                            database.
                        </p>
                    </div>

                    <Button variant="outline" asChild className="shrink-0">
                        <Link href="/pyramid/ingest">
                            <FileUp />
                            Upload another PDF
                        </Link>
                    </Button>
                </div>

                <div className="grid gap-3 sm:grid-cols-3">
                    <Card className="border-sidebar-border/70 shadow-none">
                        <CardHeader className="pb-2">
                            <CardDescription>Tables</CardDescription>
                            <CardTitle className="text-2xl tabular-nums">
                                {totalTables}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="border-sidebar-border/70 shadow-none">
                        <CardHeader className="pb-2">
                            <CardDescription>Total rows</CardDescription>
                            <CardTitle className="text-2xl tabular-nums">
                                {totalRows}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="border-sidebar-border/70 shadow-none">
                        <CardHeader className="pb-2">
                            <CardDescription>Ingested datasets</CardDescription>
                            <CardTitle className="text-2xl tabular-nums">
                                {ingestedTables.length}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>

                {tables.length === 0 ? (
                    <Card className="border-sidebar-border/70 shadow-none">
                        <CardContent className="py-12 text-center">
                            <Table2 className="mx-auto mb-3 size-10 text-muted-foreground" />
                            <p className="text-sm text-muted-foreground">
                                No Pyramid knowledge tables yet. Upload a PDF to
                                populate data.
                            </p>
                            <Button asChild className="mt-4">
                                <Link href="/pyramid/ingest">Go to ingest</Link>
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="space-y-8">
                        {ingestedTables.length > 0 && (
                            <section className="space-y-4">
                                <h2 className="text-sm font-medium tracking-wide text-muted-foreground uppercase">
                                    Ingested from PDFs
                                </h2>
                                <div className="space-y-4">
                                    {ingestedTables.map((table) => (
                                        <TableCard
                                            key={table.table}
                                            table={table}
                                        />
                                    ))}
                                </div>
                            </section>
                        )}

                        {domainTables.length > 0 && (
                            <section className="space-y-4">
                                <h2 className="text-sm font-medium tracking-wide text-muted-foreground uppercase">
                                    Operational domain tables (with data)
                                </h2>
                                <div className="space-y-4">
                                    {domainTables.map((table) => (
                                        <TableCard
                                            key={table.table}
                                            table={table}
                                        />
                                    ))}
                                </div>
                            </section>
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

function TableCard({ table }: { table: KnowledgeTable }) {
    const columns =
        table.display_columns.length > 0
            ? table.display_columns
            : table.columns.map((column) => column.name);

    return (
        <Card className="overflow-hidden border-sidebar-border/70 shadow-none">
            <CardHeader className="border-b border-sidebar-border/50 bg-muted/20">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="text-lg">{table.label}</CardTitle>
                        <CardDescription className="font-mono text-xs">
                            {table.table}
                        </CardDescription>
                    </div>
                    <div className="flex items-center gap-2">
                        <Badge
                            variant={
                                table.kind === 'ingested'
                                    ? 'default'
                                    : 'secondary'
                            }
                        >
                            {table.kind === 'ingested'
                                ? 'PDF ingest'
                                : 'Domain'}
                        </Badge>
                        <Badge variant="outline">
                            {table.row_count} row
                            {table.row_count === 1 ? '' : 's'}
                        </Badge>
                    </div>
                </div>
            </CardHeader>

            <CardContent className="p-0">
                {table.rows.length === 0 ? (
                    <p className="px-6 py-8 text-sm text-muted-foreground">
                        Table exists but has no rows yet.
                    </p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[640px] text-left text-sm">
                            <thead className="border-b border-sidebar-border/50 bg-muted/30 text-xs tracking-wide text-muted-foreground uppercase">
                                <tr>
                                    {columns.map((column) => (
                                        <th
                                            key={column}
                                            className="px-4 py-3 font-medium"
                                        >
                                            {column.replace(/_/g, ' ')}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {table.rows.map((row, index) => (
                                    <tr
                                        key={`${table.table}-${index}`}
                                        className="border-b border-sidebar-border/40 last:border-0"
                                    >
                                        {columns.map((column) => (
                                            <td
                                                key={column}
                                                className="px-4 py-3 align-top text-sm leading-relaxed"
                                            >
                                                {formatCell(row[column])}
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
