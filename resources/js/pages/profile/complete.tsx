import { Head, useForm } from '@inertiajs/react';
import { Camera, Loader2 } from 'lucide-react';
import { useRef, useState } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type ProfileData = {
    name: string;
    email: string;
    phone: string | null;
    avatar_url: string | null;
    job_title: string | null;
    company: string | null;
    location: string | null;
    website: string | null;
    bio: string | null;
};

function initials(name: string): string {
    return name
        .split(' ')
        .map((word) => word[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

export default function ProfileComplete({
    profile,
    completion,
}: {
    profile: ProfileData;
    completion: number;
}) {
    const fileInput = useRef<HTMLInputElement>(null);
    const [preview, setPreview] = useState<string | null>(profile.avatar_url);

    const { data, setData, post, processing, errors } = useForm<{
        name: string;
        phone: string;
        job_title: string;
        company: string;
        location: string;
        website: string;
        bio: string;
        avatar: File | null;
    }>({
        name: profile.name ?? '',
        phone: profile.phone ?? '',
        job_title: profile.job_title ?? '',
        company: profile.company ?? '',
        location: profile.location ?? '',
        website: profile.website ?? '',
        bio: profile.bio ?? '',
        avatar: null,
    });

    const pickFile = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] ?? null;
        setData('avatar', file);
        setPreview(file ? URL.createObjectURL(file) : profile.avatar_url);
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post('/profile/complete', { forceFormData: true, preserveScroll: true });
    };

    return (
        <div className="mx-auto w-full max-w-2xl p-4 md:p-8">
            <Head title="Complete your profile" />

            <Heading
                title="Your profile"
                description="Add a photo and a few details so the Pyramid team knows who they're working with."
            />

            <div className="mt-4 mb-8">
                <div className="mb-1.5 flex items-center justify-between text-sm">
                    <span className="font-medium text-muted-foreground">
                        Profile completion
                    </span>
                    <span className="font-semibold text-emerald-600">
                        {completion}%
                    </span>
                </div>
                <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        className="h-full rounded-full bg-emerald-500 transition-all duration-500"
                        style={{ width: `${completion}%` }}
                    />
                </div>
            </div>

            <form onSubmit={submit} className="space-y-6">
                <div className="flex items-center gap-5">
                    <div className="relative">
                        <div className="flex size-20 items-center justify-center overflow-hidden rounded-full border bg-muted text-xl font-semibold text-muted-foreground">
                            {preview ? (
                                <img
                                    src={preview}
                                    alt="Avatar preview"
                                    className="size-full object-cover"
                                />
                            ) : (
                                initials(data.name || profile.name || '?')
                            )}
                        </div>
                        <button
                            type="button"
                            onClick={() => fileInput.current?.click()}
                            className="absolute -right-1 -bottom-1 flex size-7 items-center justify-center rounded-full border bg-background text-foreground shadow-sm transition-colors hover:bg-accent"
                            aria-label="Upload photo"
                        >
                            <Camera className="size-3.5" />
                        </button>
                        <input
                            ref={fileInput}
                            type="file"
                            accept="image/*"
                            className="hidden"
                            onChange={pickFile}
                        />
                    </div>
                    <div>
                        <p className="text-sm font-medium">Profile photo</p>
                        <p className="text-xs text-muted-foreground">
                            JPG, PNG or GIF — up to 4&nbsp;MB.
                        </p>
                        <InputError className="mt-1" message={errors.avatar} />
                    </div>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="name">Full name</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        placeholder="Full name"
                    />
                    <InputError message={errors.name} />
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="job_title">Job title</Label>
                        <Input
                            id="job_title"
                            value={data.job_title}
                            onChange={(e) => setData('job_title', e.target.value)}
                            placeholder="e.g. Events Lead"
                        />
                        <InputError message={errors.job_title} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="company">Company / organization</Label>
                        <Input
                            id="company"
                            value={data.company}
                            onChange={(e) => setData('company', e.target.value)}
                            placeholder="e.g. TUMO Tirana"
                        />
                        <InputError message={errors.company} />
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="phone">Phone</Label>
                        <Input
                            id="phone"
                            value={data.phone}
                            onChange={(e) => setData('phone', e.target.value)}
                            placeholder="+355 …"
                        />
                        <InputError message={errors.phone} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="location">Location</Label>
                        <Input
                            id="location"
                            value={data.location}
                            onChange={(e) => setData('location', e.target.value)}
                            placeholder="e.g. Tirana, Albania"
                        />
                        <InputError message={errors.location} />
                    </div>
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="website">Website</Label>
                    <Input
                        id="website"
                        value={data.website}
                        onChange={(e) => setData('website', e.target.value)}
                        placeholder="https://…"
                    />
                    <InputError message={errors.website} />
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="bio">About you</Label>
                    <textarea
                        id="bio"
                        value={data.bio}
                        onChange={(e) => setData('bio', e.target.value)}
                        rows={4}
                        maxLength={1000}
                        placeholder="A short introduction…"
                        className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none"
                    />
                    <InputError message={errors.bio} />
                </div>

                <div className="flex items-center gap-3">
                    <Button disabled={processing}>
                        {processing && (
                            <Loader2 className="size-4 animate-spin" />
                        )}
                        Save profile
                    </Button>
                </div>
            </form>
        </div>
    );
}

ProfileComplete.layout = {
    breadcrumbs: [{ title: 'Profile', href: '/profile/complete' }],
};
