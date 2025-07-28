    // Convert database time to proper format for time input
    export const formatTimeForInput = (timeStr) => {
        if (!timeStr) return '';
        
        // If time is already in 24-hour format (HH:MM)
        if (/^\d{2}:\d{2}$/.test(timeStr)) {
            return timeStr;
        }
        
        // If time is in 12-hour format with AM/PM
        if (/^\d{1,2}:\d{2} [AP]M$/i.test(timeStr)) {
            const [time, period] = timeStr.split(' ');
            let [hours, minutes] = time.split(':');
            
            hours = parseInt(hours, 10);
            if (period.toUpperCase() === 'PM' && hours < 12) {
                hours += 12;
            } else if (period.toUpperCase() === 'AM' && hours === 12) {
                hours = 0;
            }
            
            return `${hours.toString().padStart(2, '0')}:${minutes}`;
        }
        
        return timeStr;
    }