export function useCurrency() {
  const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
    }).format(amount / 100);
  };

  const parseCurrency = (value: string) => {
    const number = parseFloat(value.replace(/[^0-9.-]+/g, ""));
    return isNaN(number) ? 0 : Math.round(number * 100);
  };

  return { formatCurrency, parseCurrency };
}
