import { Form, Head } from '@inertiajs/react';
import { Building2, ChevronLeft, ChevronRight, HardHat } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import PasskeyVerify from '@/components/passkey-verify';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type AccountType = 'organization' | 'operational';

type Tenant = {
    id: number;
    title: string;
    description: string | null;
    roles: string[];
};

type Props = {
    status?: string;
    canResetPassword: boolean;
    tenants: Tenant[];
};

type Step = 'account' | 'tenant' | 'role' | 'credentials';

export default function Login({
    status,
    canResetPassword,
    tenants = [],
}: Props) {
    const [accountType, setAccountType] = useState<AccountType | null>(null);
    const [tenantId, setTenantId] = useState<number | null>(null);
    const [workerRole, setWorkerRole] = useState<string | null>(null);

    const selectedTenant = useMemo(
        () => tenants.find((tenant) => tenant.id === tenantId) ?? null,
        [tenants, tenantId],
    );

    const step: Step = !accountType
        ? 'account'
        : accountType === 'organization'
          ? 'credentials'
          : !tenantId
            ? 'tenant'
            : !workerRole
              ? 'role'
              : 'credentials';

    const goBack = () => {
        switch (step) {
            case 'tenant':
                setAccountType(null);
                break;
            case 'role':
                setTenantId(null);
                break;
            case 'credentials':
                if (accountType === 'operational') {
                    setWorkerRole(null);
                } else {
                    setAccountType(null);
                }

                break;
        }
    };

    return (
        <>
            <Head title="Log in" />

            {step !== 'account' && (
                <button
                    type="button"
                    onClick={goBack}
                    className="mb-4 -ml-1 inline-flex w-fit items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                >
                    <ChevronLeft className="size-4" />
                    Back
                </button>
            )}

            {accountType === 'operational' && (
                <OperationalProgress step={step} />
            )}

            {step === 'account' && (
                <div className="flex flex-col gap-3">
                    <AccountTile
                        icon={Building2}
                        title="Organization"
                        description="Plan and submit events as an external organization."
                        onClick={() => setAccountType('organization')}
                    />
                    <AccountTile
                        icon={HardHat}
                        title="Operational worker"
                        description="Sign into a Pyramid branch as a tenant-based worker."
                        onClick={() => setAccountType('operational')}
                    />
                </div>
            )}

            {step === 'tenant' && (
                <div className="flex flex-col gap-3">
                    <p className="text-sm font-medium text-foreground">
                        Choose your branch
                    </p>
                    {tenants.length > 0 ? (
                        tenants.map((tenant) => (
                            <TenantTile
                                key={tenant.id}
                                tenant={tenant}
                                onClick={() => setTenantId(tenant.id)}
                            />
                        ))
                    ) : (
                        <div className="rounded-xl border border-dashed bg-muted/30 p-6 text-center text-sm text-muted-foreground">
                            No branches are available yet. Run the database
                            seeder to add them.
                        </div>
                    )}
                </div>
            )}

            {step === 'role' && selectedTenant && (
                <div className="flex flex-col gap-3">
                    <p className="text-sm font-medium text-foreground">
                        Your role at{' '}
                        <span className="text-muted-foreground">
                            {selectedTenant.title}
                        </span>
                    </p>
                    <div className="grid grid-cols-2 gap-2">
                        {selectedTenant.roles.map((role) => (
                            <button
                                key={role}
                                type="button"
                                onClick={() => setWorkerRole(role)}
                                className="rounded-lg border bg-card px-3 py-3 text-left text-sm font-medium transition-colors hover:border-primary hover:bg-accent focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            >
                                {role}
                            </button>
                        ))}
                    </div>
                </div>
            )}

            {step === 'credentials' && (
                <Credentials
                    accountType={accountType!}
                    tenantId={tenantId}
                    workerRole={workerRole}
                    selectedTenant={selectedTenant}
                    canResetPassword={canResetPassword}
                />
            )}

            {step === 'account' && (
                <div className="mt-6 text-center text-sm text-muted-foreground">
                    Don't have an account?{' '}
                    <TextLink href={register()}>Sign up</TextLink>
                </div>
            )}

            {status && (
                <div className="mt-4 text-center text-sm font-medium text-emerald-600">
                    {status}
                </div>
            )}
        </>
    );
}

function Credentials({
    accountType,
    tenantId,
    workerRole,
    selectedTenant,
    canResetPassword,
}: {
    accountType: AccountType;
    tenantId: number | null;
    workerRole: string | null;
    selectedTenant: Tenant | null;
    canResetPassword: boolean;
}) {
    return (
        <div className="flex flex-col gap-6">
            <div className="flex flex-wrap items-center gap-1.5">
                <Badge variant="secondary" className="capitalize">
                    {accountType}
                </Badge>
                {accountType === 'operational' && selectedTenant && (
                    <>
                        <ChevronRight className="size-3.5 text-muted-foreground" />
                        <Badge variant="secondary">
                            {selectedTenant.title}
                        </Badge>
                        <ChevronRight className="size-3.5 text-muted-foreground" />
                        <Badge variant="secondary">{workerRole}</Badge>
                    </>
                )}
            </div>

            {accountType === 'organization' && <PasskeyVerify />}

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <input
                            type="hidden"
                            name="account_type"
                            value={accountType}
                        />
                        {accountType === 'operational' && (
                            <>
                                <input
                                    type="hidden"
                                    name="tenant_id"
                                    value={tenantId ?? ''}
                                />
                                <input
                                    type="hidden"
                                    name="worker_role"
                                    value={workerRole ?? ''}
                                />
                            </>
                        )}

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="email"
                                placeholder="email@example.com"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <div className="flex items-center">
                                <Label htmlFor="password">Password</Label>
                                {canResetPassword && (
                                    <TextLink
                                        href={request()}
                                        className="ml-auto text-sm"
                                        tabIndex={5}
                                    >
                                        Forgot your password?
                                    </TextLink>
                                )}
                            </div>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                tabIndex={2}
                                autoComplete="current-password"
                                placeholder="Password"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center space-x-3">
                            <Checkbox
                                id="remember"
                                name="remember"
                                tabIndex={3}
                            />
                            <Label htmlFor="remember">Remember me</Label>
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            tabIndex={4}
                            disabled={processing}
                            data-test="login-button"
                        >
                            {processing && <Spinner />}
                            Log in
                        </Button>
                    </>
                )}
            </Form>
        </div>
    );
}

function AccountTile({
    icon: Icon,
    title,
    description,
    onClick,
}: {
    icon: LucideIcon;
    title: string;
    description: string;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="group flex w-full items-center gap-4 rounded-xl border bg-card p-4 text-left transition-all hover:border-primary hover:shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        >
            <span className="flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <Icon className="size-5" />
            </span>
            <span className="flex-1">
                <span className="block font-medium">{title}</span>
                <span className="block text-sm text-muted-foreground">
                    {description}
                </span>
            </span>
            <ChevronRight className="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:text-foreground" />
        </button>
    );
}

function TenantTile({
    tenant,
    onClick,
}: {
    tenant: Tenant;
    onClick: () => void;
}) {
    const initials = tenant.title
        .split(' ')
        .map((word) => word[0])
        .slice(0, 2)
        .join('');

    return (
        <button
            type="button"
            onClick={onClick}
            className="group flex w-full items-center gap-4 rounded-xl border bg-card p-4 text-left transition-all hover:border-primary hover:shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
        >
            <span className="flex size-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-fuchsia-500 text-sm font-semibold text-white">
                {initials}
            </span>
            <span className="flex-1">
                <span className="block font-medium">{tenant.title}</span>
                {tenant.description && (
                    <span className="line-clamp-2 block text-sm text-muted-foreground">
                        {tenant.description}
                    </span>
                )}
            </span>
            <ChevronRight className="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5 group-hover:text-foreground" />
        </button>
    );
}

const OPERATIONAL_STEPS: Step[] = ['tenant', 'role', 'credentials'];

function OperationalProgress({ step }: { step: Step }) {
    const currentIndex = OPERATIONAL_STEPS.indexOf(step);

    return (
        <div className="mb-6 flex items-center gap-2">
            {OPERATIONAL_STEPS.map((value, index) => (
                <span
                    key={value}
                    className={cn(
                        'h-1.5 flex-1 rounded-full transition-colors',
                        index <= currentIndex ? 'bg-primary' : 'bg-muted',
                    )}
                />
            ))}
        </div>
    );
}

Login.layout = {
    title: 'Sign in',
    description: 'Choose how you want to access Pyramid Backstage.',
};
