export type SelectOption = {
    value: string;
    label: string;
};

export type EntityOption = {
    id: number;
    name: string;
    category_id?: number;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginatedResponse<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
};
