import { Controller } from '@hotwired/stimulus';

export default class extends Controller {

    static targets = ['input']

    setOneYear(date, plus)
    {
        let newYear = date.getFullYear();
        newYear += plus ? 1 : -1;
        date.setFullYear(newYear);
        return date;
    }

    setOneMonth(date, plus)
    {
        let newMonth = date.getMonth();
        newMonth += plus ? 1 : -1;
        date.setUTCMonth(newMonth);
        return date;
    }

    setOneWeek(date, plus)
    {
        let newDays = date.getDate();
        newDays += plus ? 7 : -7;
        date.setDate(newDays);
        return date;
    }


    set(e)
    {
        e.preventDefault();
        const data = e.target.dataset.days;
        if(data)
        {
            let date = new Date(this.inputTarget.value === "" ? new Date() : this.inputTarget.value);
            switch (data)
            {
                case '+year':
                    this.inputTarget.value = this.setOneYear(date, true).toISOString().substring(0, 10);
                    break;
                case '-year':
                    this.inputTarget.value = this.setOneYear(date, false).toISOString().substring(0, 10);
                    break;
                case '+month':
                    this.inputTarget.value = this.setOneMonth(date, true).toISOString().substring(0, 10);
                    break;
                case '-month':
                    this.inputTarget.value = this.setOneMonth(date, false).toISOString().substring(0, 10);
                    break;
                case '+week':
                    this.inputTarget.value = this.setOneWeek(date, true).toISOString().substring(0, 10);
                    break;
                case '-week':
                    this.inputTarget.value = this.setOneWeek(date, false).toISOString().substring(0, 10);
                    break;
            }
        }
    }

}
