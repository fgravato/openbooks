export function useLocalStorage() {
  const get = <T>(key: string, defaultValue: T): T => {
    const value = localStorage.getItem(key);
    if (!value) return defaultValue;
    try {
      return JSON.parse(value) as T;
    } catch (e) {
      return defaultValue;
    }
  };

  const set = (key: string, value: any) => {
    localStorage.setItem(key, JSON.stringify(value));
  };

  const remove = (key: string) => {
    localStorage.removeItem(key);
  };

  return { get, set, remove };
}
