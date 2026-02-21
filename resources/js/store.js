export const cleanCardName = (name, category) => {
    if (!name) return 'Unknown';
    if (!category) return name;
    const safeCategory = category.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`^${safeCategory}\\s+`, 'i');
    return name.replace(regex, '').trim();
};