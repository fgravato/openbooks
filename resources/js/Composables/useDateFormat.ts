import { format, formatDistanceToNow, parseISO } from 'date-fns';

export function useDateFormat() {
  const formatDate = (date: string | Date, formatStr: string = 'PPP') => {
    const d = typeof date === 'string' ? parseISO(date) : date;
    return format(d, formatStr);
  };

  const formatDateTime = (date: string | Date) => {
    return formatDate(date, 'PPP p');
  };

  const relativeTime = (date: string | Date) => {
    const d = typeof date === 'string' ? parseISO(date) : date;
    return formatDistanceToNow(d, { addSuffix: true });
  };

  return { formatDate, formatDateTime, relativeTime };
}
