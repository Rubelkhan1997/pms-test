/**
 * Validation Utility Functions
 * Form validation rules and helpers
 */

export interface ValidationResult {
    valid: boolean;
    message?: string;
}

export type ValidationRule = (value: any) => ValidationResult | boolean;

/**
 * Check if value is required
 */
export function required(value: any): ValidationResult {
    if (typeof value === 'string') {
        return {
            valid: value.trim().length > 0,
            message: 'This field is required'
        };
    }
    return {
        valid: value !== null && value !== undefined && value !== '',
        message: 'This field is required'
    };
}

/**
 * Validate email format
 */
export function email(value: string): ValidationResult {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return {
        valid: pattern.test(value),
        message: 'Please enter a valid email address'
    };
}

/**
 * Validate phone number (Bangladesh format)
 */
export function phone(value: string): ValidationResult {
    const pattern = /^(\+880|880|0)?[1-9][0-9]{9}$/;
    return {
        valid: pattern.test(value.replace(/[\s-]/g, '')),
        message: 'Please enter a valid phone number'
    };
}

/**
 * Validate minimum length
 */
export function minLength(length: number): ValidationRule {
    return (value: string): ValidationResult => ({
        valid: value.length >= length,
        message: `Must be at least ${length} characters`
    });
}

/**
 * Validate maximum length
 */
export function maxLength(length: number): ValidationRule {
    return (value: string): ValidationResult => ({
        valid: value.length <= length,
        message: `Must not exceed ${length} characters`
    });
}

/**
 * Validate exact length
 */
export function length(length: number): ValidationRule {
    return (value: string): ValidationResult => ({
        valid: value.length === length,
        message: `Must be exactly ${length} characters`
    });
}

/**
 * Validate minimum value (numbers)
 */
export function minValue(min: number): ValidationRule {
    return (value: number): ValidationResult => ({
        valid: value >= min,
        message: `Must be at least ${min}`
    });
}

/**
 * Validate maximum value (numbers)
 */
export function maxValue(max: number): ValidationRule {
    return (value: number): ValidationResult => ({
        valid: value <= max,
        message: `Must not exceed ${max}`
    });
}

/**
 * Validate number pattern
 */
export function number(value: string | number): ValidationResult {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return {
        valid: !isNaN(num) && isFinite(num),
        message: 'Please enter a valid number'
    };
}

/**
 * Validate integer
 */
export function integer(value: number | string): ValidationResult {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return {
        valid: Number.isInteger(num),
        message: 'Please enter a whole number'
    };
}

/**
 * Validate positive number
 */
export function positive(value: number): ValidationResult {
    return {
        valid: value > 0,
        message: 'Value must be positive'
    };
}

/**
 * Validate date is in the future
 */
export function futureDate(value: string | Date): ValidationResult {
    const date = typeof value === 'string' ? new Date(value) : value;
    return {
        valid: date.getTime() > new Date().getTime(),
        message: 'Date must be in the future'
    };
}

/**
 * Validate date is in the past
 */
export function pastDate(value: string | Date): ValidationResult {
    const date = typeof value === 'string' ? new Date(value) : value;
    return {
        valid: date.getTime() < new Date().getTime(),
        message: 'Date must be in the past'
    };
}

/**
 * Validate date range (check-in before check-out)
 */
export function dateRange(start: string | Date, end: string | Date): ValidationResult {
    const startDate = typeof start === 'string' ? new Date(start) : start;
    const endDate = typeof end === 'string' ? new Date(end) : end;
    return {
        valid: startDate.getTime() < endDate.getTime(),
        message: 'Check-out date must be after check-in date'
    };
}

/**
 * Validate matching fields (e.g., password confirmation)
 */
export function matches(fieldValue: string, confirmFieldValue: string): ValidationResult {
    return {
        valid: fieldValue === confirmFieldValue,
        message: 'Fields do not match'
    };
}

/**
 * Validate URL format
 */
export function url(value: string): ValidationResult {
    const pattern = /^https?:\/\/.+/;
    return {
        valid: pattern.test(value),
        message: 'Please enter a valid URL'
    };
}

/**
 * Validate alphanumeric
 */
export function alphanumeric(value: string): ValidationResult {
    const pattern = /^[a-zA-Z0-9]+$/;
    return {
        valid: pattern.test(value),
        message: 'Only letters and numbers are allowed'
    };
}

/**
 * Validate with custom regex pattern
 */
export function pattern(value: string, regex: RegExp, message: string): ValidationResult {
    return {
        valid: regex.test(value),
        message
    };
}

/**
 * Validate file size (in bytes)
 */
export function fileSize(maxSize: number): ValidationRule {
    return (file: File): ValidationResult => ({
        valid: file.size <= maxSize,
        message: `File size must not exceed ${formatFileSize(maxSize)}`
    });
}

/**
 * Validate file type
 */
export function fileType(allowedTypes: string[]): ValidationRule {
    return (file: File): ValidationResult => ({
        valid: allowedTypes.includes(file.type),
        message: `Allowed file types: ${allowedTypes.join(', ')}`
    });
}

/**
 * Compose multiple validators
 */
export function compose(...validators: ValidationRule[]): ValidationRule {
    return (value: any): ValidationResult => {
        for (const validator of validators) {
            const result = validator(value);
            if (typeof result === 'boolean' && !result) {
                return { valid: false, message: 'Validation failed' };
            }
            if (typeof result === 'object' && !result.valid) {
                return result;
            }
        }
        return { valid: true };
    };
}

/**
 * Format file size helper for validation messages
 */
function formatFileSize(bytes: number): string {
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${(bytes / Math.pow(k, i)).toFixed(2)} ${sizes[i]}`;
}
