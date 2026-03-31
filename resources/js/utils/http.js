export function normalizeItems(payload) {
    if (Array.isArray(payload)) {
        return payload;
    }

    if (Array.isArray(payload?.data)) {
        return payload.data;
    }

    return [];
}

export function extractMessage(error, fallback = 'Something went wrong.') {
    return error?.response?.data?.message
        || Object.values(error?.response?.data?.errors || {})?.[0]?.[0]
        || fallback;
}
