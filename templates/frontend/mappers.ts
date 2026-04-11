// FILE: resources/js/Utils/Mappers/[model_name].ts

import type {
    [MODEL_NAME],
    [MODEL_NAME]Filters,
    [MODEL_NAME]Pagination,
    Create[MODEL_NAME]Dto,
    Update[MODEL_NAME]Dto,
} from '@/Types/[MODULE_NAME]/[model_name]';

/**
 * Map API response to [MODEL_NAME] interface
 */
export function mapTo[MODEL_NAME](api: Record<string, any>): [MODEL_NAME] {
    return {
        id: api.id,
        // [FIELDS - snake_case to camelCase]
        // Example:
        // name: api.name,
        // code: api.code,
        // email: api.email || null,
        // phone: api.phone || null,
        // address: api.address || null,
        // status: api.status || api.status_value || 'pending',
        // statusLabel: api.status_label,
        createdAt: api.created_at,
        updatedAt: api.updated_at,
    };
}

/**
 * Map API pagination to [MODEL_NAME]Pagination interface
 */
export function mapTo[MODEL_NAME]Pagination(api: Record<string, any>): [MODEL_NAME]Pagination {
    return {
        currentPage: api.current_page || 1,
        perPage: api.per_page || 15,
        total: api.total || 0,
        lastPage: api.last_page || 1,
    };
}

/**
 * Map filters to API query params
 */
export function mapTo[MODEL_NAME]FiltersApi(filters: [MODEL_NAME]Filters): Record<string, any> {
    return {
        search: filters.search || undefined,
        // [ADDITIONAL_FILTERS]
        // Example:
        // status: filters.status || undefined,
    };
}

/**
 * Map Create DTO to API payload
 */
export function mapCreate[MODEL_NAME]ToApi(dto: Create[MODEL_NAME]Dto): Record<string, any> {
    return {
        // [FIELDS]
        // Example:
        // name: dto.name,
        // code: dto.code,
        // email: dto.email,
        // phone: dto.phone,
        // address: dto.address,
        // status: dto.status,
    };
}

/**
 * Map Update DTO to API payload
 */
export function mapUpdate[MODEL_NAME]ToApi(dto: Update[MODEL_NAME]Dto): Record<string, any> {
    return {
        // [FIELDS]
        // Example:
        // name: dto.name,
        // code: dto.code,
        // email: dto.email,
        // phone: dto.phone,
        // address: dto.address,
        // status: dto.status,
    };
}
